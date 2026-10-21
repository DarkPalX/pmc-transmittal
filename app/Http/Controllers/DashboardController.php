<?php

namespace App\Http\Controllers;

use App\Models\TransmittalHeader;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'total' => TransmittalHeader::count(),
            'outgoing' => TransmittalHeader::where('transmittal_type', 'Outgoing')->count(),
            'incoming' => TransmittalHeader::where('transmittal_type', 'Incoming')->count(),
            'pending' => TransmittalHeader::where('status', 'Saved')->count(),
            'recent' => TransmittalHeader::latest('transmittal_date')->latest('id')->limit(6)->get(),
        ]);
    }
}
