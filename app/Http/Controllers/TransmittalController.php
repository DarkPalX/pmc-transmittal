<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TransmittalHeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransmittalController extends Controller
{
    public function index(Request $request): View
    {
        $query = TransmittalHeader::query();
        if ($request->filled('type')) $query->where('transmittal_type', $request->string('type'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('q')) $query->where(fn ($q) => $q->where('ref_no', 'like', '%'.$request->string('q').'%')->orWhere('destination', 'like', '%'.$request->string('q').'%'));
        return view('transmittals.index', ['transmittals' => $query->with('details')->latest('transmittal_date')->paginate(12)->withQueryString()]);
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
        $data['status'] = $data['status'] ?? 'Draft';
        $data['attachment'] = $request->hasFile('attachment') ? $request->file('attachment')->store('transmittals', 'public') : null;
        $header = DB::transaction(function () use ($data, $request) {
            $header = TransmittalHeader::create($data);
            foreach ($request->input('details', []) as $detail) {
                if (filled($detail['item_desc'] ?? null)) $header->details()->create($detail);
            }
            return $header;
        });
        return redirect()->route('transmittals.show', $header)->with('success', 'Transmittal created.');
    }

    public function show(TransmittalHeader $transmittal): View
    {
        $transmittal->load('details');
        return view('transmittals.show', compact('transmittal'));
    }

    public function edit(TransmittalHeader $transmittal): View
    {
        return view('transmittals.form', ['transmittal' => $transmittal->load('details'), 'items' => Item::where('is_active', true)->orderBy('description')->get()]);
    }

    public function update(Request $request, TransmittalHeader $transmittal): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('attachment')) {
            if ($transmittal->attachment) Storage::disk('public')->delete($transmittal->attachment);
            $data['attachment'] = $request->file('attachment')->store('transmittals', 'public');
        }
        DB::transaction(function () use ($data, $request, $transmittal) {
            $transmittal->update($data);
            $transmittal->details()->delete();
            foreach ($request->input('details', []) as $detail) if (filled($detail['item_desc'] ?? null)) $transmittal->details()->create($detail);
        });
        return redirect()->route('transmittals.show', $transmittal)->with('success', 'Transmittal updated.');
    }

    public function destroy(TransmittalHeader $transmittal): RedirectResponse
    {
        $transmittal->delete();
        return redirect()->route('transmittals.index')->with('success', 'Transmittal deleted.');
    }

    public function print(TransmittalHeader $transmittal): View
    {
        return view('transmittals.print', ['transmittal' => $transmittal->load('details')]);
    }

    public function updateStatus(Request $request, TransmittalHeader $transmittal): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:Draft,For Receiving,Received,Closed'],
        ]);

        $transmittal->update($data);

        return back()->with('success', "{$transmittal->ref_no} status updated to {$transmittal->status}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'ref_no' => ['required', 'string', 'max:100', 'unique:transmittal_header,ref_no,'.$request->route('transmittal')?->id],
            'transmittal_date' => ['required', 'date'], 'transmittal_type' => ['required', 'in:Incoming,Outgoing'],
            'destination' => ['required', 'string', 'max:255'], 'plate_no' => ['nullable', 'string', 'max:50'],
            'prepared_by' => ['nullable', 'string', 'max:255'], 'received_by' => ['nullable', 'string', 'max:255'],
            'noted_by' => ['nullable', 'string', 'max:255'], 'status' => ['required', 'in:Draft,For Receiving,Received,Closed'],
            'remarks' => ['nullable', 'string'], 'attachment' => ['nullable', 'file', 'max:10240'],
            'details' => ['array'], 'details.*.item_id' => ['nullable', 'integer'], 'details.*.item_desc' => ['required', 'string'],
            'details.*.item_unit' => ['nullable', 'string', 'max:50'], 'details.*.item_serial_no' => ['nullable', 'string', 'max:100'], 'details.*.remarks' => ['nullable', 'string'],
        ]);
    }
}
