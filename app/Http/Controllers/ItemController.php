<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TransmittalDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'current')->toString();
        $search = trim($request->string('q')->toString());
        if (in_array($filter, ['outgoing', 'all'], true)) {
            $outgoing = TransmittalDetail::with('header', 'item')
                ->when($filter === 'outgoing', fn ($query) => $query->whereHas('header', fn ($header) => $header->where('transmittal_type', 'Outgoing')->where('status', 'Transmitted')))
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('item_desc', 'like', "%{$search}%")
                        ->orWhere('item_serial_no', 'like', "%{$search}%")
                        ->orWhere('par_no', 'like', "%{$search}%")
                        ->orWhereHas('header', fn ($header) => $header->where('ref_no', 'like', "%{$search}%")->orWhere('destination', 'like', "%{$search}%"));
                }))
                ->latest()->paginate(15)->withQueryString();

            $outgoing->getCollection()->each(function (TransmittalDetail $detail): void {
                if ($detail->item) return;

                $item = Item::query()
                    ->where('description', $detail->item_desc)
                    ->where('unit', $detail->item_unit)
                    ->when(filled($detail->item_serial_no), fn ($query) => $query->where('serial_no', $detail->item_serial_no))
                    ->when(! filled($detail->item_serial_no), fn ($query) => $query->whereNull('serial_no'))
                    ->first();

                if ($item) {
                    $detail->setRelation('item', $item);
                } else {
                    $virtualItem = new Item([
                        'item_code' => 'Movement history',
                        'description' => $detail->item_desc,
                        'unit' => $detail->item_unit,
                        'serial_no' => $detail->item_serial_no,
                    ]);
                    $virtualItem->setAttribute('route_key', 'movement-'.$detail->id);
                    $detail->setRelation('item', $virtualItem);
                }
            });

            return view('items.index', ['items' => collect(), 'outgoing' => $outgoing, 'filter' => $filter]);
        }

        $items = Item::query()
                ->where('is_active', $filter !== 'archived')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('item_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('serial_no', 'like', "%{$search}%")
                        ->orWhere('par_no', 'like', "%{$search}%");
                }))
                ->latest('transmittal_date')->paginate(15)->withQueryString();

        return view('items.index', [
            'items' => $items,
            'outgoing' => null,
            'filter' => $filter,
        ]);
    }

    public function history(string $item): View
    {
        $view = request()->string('view', 'split')->toString();
        if (str_starts_with($item, 'movement-')) {
            $detail = TransmittalDetail::with('header')->findOrFail((int) substr($item, strlen('movement-')));
            $catalogItem = new Item([
                'item_code' => 'Movement history',
                'description' => $detail->item_desc,
                'unit' => $detail->item_unit,
                'serial_no' => $detail->item_serial_no,
            ]);
            $catalogItem->setAttribute('route_key', 'movement-'.$detail->id);
            $history = fn () => TransmittalDetail::with('header')
                ->where('item_desc', $detail->item_desc)
                ->where('item_unit', $detail->item_unit)
                ->when(filled($detail->item_serial_no), fn ($query) => $query->where('item_serial_no', $detail->item_serial_no))
                ->when(! filled($detail->item_serial_no), fn ($query) => $query->whereNull('item_serial_no'));
        } else {
            $catalogItem = Item::findOrFail($item);
            $history = fn () => TransmittalDetail::with('header')->where('item_id', $catalogItem->id);
        }

        $incoming = $history()->whereHas('header', fn ($query) => $query->where('transmittal_type', 'Incoming'))->latest()->get();
        $outgoing = $history()->whereHas('header', fn ($query) => $query->where('transmittal_type', 'Outgoing'))->latest()->get();

        return view('items.history', [
            'item' => $catalogItem,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'activity' => $incoming->merge($outgoing)->sortByDesc(fn ($detail) => $detail->header->transmittal_date)->values(),
            'view' => in_array($view, ['split', 'all'], true) ? $view : 'split',
        ]);
    }
    public function destroy(Item $item): RedirectResponse { $item->update(['is_active' => false]); return back()->with('success', 'Item archived.'); }
    public function restore(Item $item): RedirectResponse { $item->update(['is_active' => true]); return back()->with('success', 'Item restored to the current catalog.'); }
}
