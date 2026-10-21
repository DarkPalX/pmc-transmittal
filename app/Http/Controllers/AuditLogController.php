<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user', 'auditable')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('q'), fn ($query) => $query->where('description', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
