<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\AuditLog;
use App\Models\TransmittalHeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransmittalController extends Controller
{
    public function index(Request $request): View
    {
        $query = TransmittalHeader::query();
        if ($request->filled('type')) $query->where('transmittal_type', $request->string('type'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('q')) $query->where(fn ($q) => $q->where('ref_no', 'like', '%'.$request->string('q').'%')->orWhere('destination', 'like', '%'.$request->string('q').'%'));
        $sort = $request->get('sort', 'updated_desc');
        $sorts = [
            'updated_desc' => ['updated_at', 'desc'],
            'updated_asc' => ['updated_at', 'asc'],
            'date_desc' => ['transmittal_date', 'desc'],
            'date_asc' => ['transmittal_date', 'asc'],
            'ref_asc' => ['ref_no', 'asc'],
        ];
        [$sortColumn, $sortDirection] = $sorts[$sort] ?? $sorts['updated_desc'];

        return view('transmittals.index', ['transmittals' => $query->with('details')->orderBy($sortColumn, $sortDirection)->paginate(12)->withQueryString()]);
    }

    public function create(Request $request): View
    {
        $type = $request->get('type', 'Outgoing');
        abort_unless(in_array($type, ['Incoming', 'Outgoing'], true), 404);
        return view('transmittals.form', ['transmittal' => new TransmittalHeader(['transmittal_type' => $type, 'transmittal_date' => now()]), 'items' => Item::where('is_active', true)->orderBy('description')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['prepared_by'] = $data['prepared_by'] ?: $request->user()->name;
        $data['status'] = $data['status'] ?? 'Saved';
        $data['attachment'] = $request->hasFile('attachment') ? $request->file('attachment')->store('transmittals', 'public') : null;
        $header = DB::transaction(function () use ($data, $request) {
            $header = TransmittalHeader::create($data);
            foreach ($request->input('details', []) as $index => $detail) {
                if (! filled($detail['item_desc'] ?? null)) continue;
                $detail['item_qty'] = filled($detail['item_qty'] ?? null) ? $detail['item_qty'] : 1;
                $detail['attachment'] = $request->file("details.$index.attachment")?->store('transmittal-details', 'public');
                $savedDetail = $header->details()->create($detail);
                if ($header->transmittal_type === 'Incoming') $this->saveIncomingItem($savedDetail, $header);
            }
            return $header;
        });
        $this->audit($header, 'created', 'Transmittal created.');
        return redirect()->route('transmittals.show', $header)->with('success', 'Transmittal created.');
    }

    public function show(TransmittalHeader $transmittal): View
    {
        $transmittal->load('details', 'auditLogs.user');
        return view('transmittals.show', compact('transmittal'));
    }

    public function edit(TransmittalHeader $transmittal): View
    {
        abort_if($transmittal->status !== 'Saved' && ! request()->user()->isSuperadmin(), 403, 'Only saved transmittals can be edited.');
        return view('transmittals.form', ['transmittal' => $transmittal->load('details'), 'items' => Item::where('is_active', true)->orderBy('description')->get()]);
    }

    public function update(Request $request, TransmittalHeader $transmittal): RedirectResponse
    {
        if ($transmittal->status !== 'Saved' && ! $request->user()->isSuperadmin()) {
            return back()->with('error', 'Only saved transmittals can be updated.');
        }
        $data = $this->validated($request);
        if ($request->hasFile('attachment')) {
            if ($transmittal->attachment) Storage::disk('public')->delete($transmittal->attachment);
            $data['attachment'] = $request->file('attachment')->store('transmittals', 'public');
        }
        $before = $transmittal->only(['ref_no', 'transmittal_date', 'transmittal_type', 'destination', 'status', 'prepared_by', 'received_by', 'noted_by', 'remarks']);
        DB::transaction(function () use ($data, $request, $transmittal) {
            $transmittal->update($data);
            $transmittal->details()->delete();
            foreach ($request->input('details', []) as $index => $detail) {
                if (! filled($detail['item_desc'] ?? null)) continue;
                $detail['item_qty'] = filled($detail['item_qty'] ?? null) ? $detail['item_qty'] : 1;
                $detail['attachment'] = $request->file("details.$index.attachment")?->store('transmittal-details', 'public');
                $savedDetail = $transmittal->details()->create($detail);
                if ($transmittal->transmittal_type === 'Incoming') $this->saveIncomingItem($savedDetail, $transmittal);
            }
        });
        $this->audit($transmittal, 'updated', 'Transmittal details updated.', $before, $transmittal->fresh()->only(array_keys($before)));
        return redirect()->route('transmittals.show', $transmittal)->with('success', 'Transmittal updated.');
    }

    public function destroy(TransmittalHeader $transmittal): RedirectResponse
    {
        $this->audit($transmittal, 'deleted', 'Transmittal deleted.');
        $transmittal->delete();
        return redirect()->route('transmittals.index')->with('success', 'Transmittal deleted.');
    }

    public function print(TransmittalHeader $transmittal): View
    {
        return view('transmittals.print', ['transmittal' => $transmittal->load('details')]);
    }

    public function updateStatus(Request $request, TransmittalHeader $transmittal): RedirectResponse
    {
        if ($transmittal->status === 'Transmitted' && ! $request->user()->isSuperadmin()) {
            return back()->with('error', 'This transmittal is already transmitted and cannot be changed.');
        }
        $data = $request->validate([
            'status' => ['required', 'in:Saved,Transmitted,Received'],
        ]);

        $oldStatus = $transmittal->status;
        $transmittal->update($data);
        $this->audit($transmittal, 'status_changed', "Status changed from {$oldStatus} to {$transmittal->status}.", ['status' => $oldStatus], ['status' => $transmittal->status]);

        return back()->with('success', "{$transmittal->ref_no} status updated to {$transmittal->status}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'ref_no' => ['required', 'string', 'max:100', 'unique:transmittal_header,ref_no,'.$request->route('transmittal')?->id],
            'transmittal_date' => ['required', 'date'], 'transmittal_type' => ['required', 'in:Incoming,Outgoing'],
            'destination' => ['required', 'string', 'max:255'], 'plate_no' => ['nullable', 'string', 'max:50'],
            'prepared_by' => ['nullable', 'string', 'max:255'], 'received_by' => ['nullable', 'string', 'max:255'],
            'noted_by' => ['nullable', 'string', 'max:255'], 'status' => ['required', 'in:Saved,Transmitted,Received'],
            'remarks' => ['nullable', 'string'], 'attachment' => ['nullable', 'file', 'max:10240'],
            'details' => ['array'], 'details.*.item_id' => ['nullable', 'integer', 'exists:items,id'], 'details.*.item_qty' => ['nullable', 'numeric', 'min:0.01'], 'details.*.item_desc' => ['required', 'string'],
            'details.*.item_unit' => ['nullable', 'string', 'max:50'], 'details.*.item_serial_no' => ['nullable', 'string', 'max:100'],
            'details.*.par_no' => ['nullable', 'string', 'max:100'], 'details.*.attachment' => ['nullable', 'file', 'max:10240'], 'details.*.remarks' => ['nullable', 'string'],
        ]);
    }

    private function saveIncomingItem($detail, TransmittalHeader $header): void
    {
        $item = $detail->item_id ? Item::find($detail->item_id) : null;
        if (! $item) {
            $itemQuery = Item::where('description', $detail->item_desc);
            if (filled($detail->item_serial_no)) {
                $itemQuery->where('serial_no', $detail->item_serial_no);
            } else {
                $itemQuery->where('unit', $detail->item_unit)->whereNull('serial_no');
            }
            $item = $itemQuery->first();
        }

        $values = [
            'description' => $detail->item_desc,
            'unit' => $detail->item_unit,
            'serial_no' => $detail->item_serial_no,
            'transmittal_date' => $header->transmittal_date,
            'par_no' => $detail->par_no,
            'attachment' => $detail->attachment,
            'is_active' => true,
        ];

        if ($item) {
            $item->update($values);
            return;
        }

        $values['item_code'] = 'IN-'.Str::upper(Str::random(8));
        Item::create($values);
    }

    private function audit(TransmittalHeader $transmittal, string $action, string $description, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => TransmittalHeader::class,
            'auditable_id' => $transmittal->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}
