<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View { return view('items.index', ['items' => Item::latest()->paginate(15)]); }
    public function store(Request $request): RedirectResponse
    {
        Item::create($request->validate(['item_code' => ['required', 'unique:items,item_code'], 'description' => ['required'], 'unit' => ['nullable'], 'serial_no' => ['nullable']]));
        return back()->with('success', 'Item added to the catalog.');
    }
    public function destroy(Item $item): RedirectResponse { $item->update(['is_active' => false]); return back()->with('success', 'Item archived.'); }
}
