@extends('layouts.app')
@section('title', 'Audit trail')
@section('content')
<div class="page-head"><div><span class="eyebrow">SYSTEM / AUDIT TRAIL</span><h1>Audit trail</h1><p class="muted">Review recorded actions across transmittals.</p></div></div>
<div class="toolbar"><form class="search" method="GET"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Search activity..."><select name="action"><option value="">All actions</option>@foreach(['created','updated','status_changed','deleted'] as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ Str::headline($action) }}</option>@endforeach</select><button class="button ghost">Filter</button></form></div>
<div class="table-card"><table><thead><tr><th>Date</th><th>Action</th><th>Activity</th><th>User</th><th>IP address</th><th>Record</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{ $log->created_at->format('d M Y, h:i A') }}</td><td><span class="status {{ Str::slug($log->action) }}">{{ Str::headline($log->action) }}</span></td><td>{{ $log->description }}</td><td>{{ $log->user?->name ?: 'System' }}</td><td>{{ $log->ip_address ?: '—' }}</td><td>@if($log->auditable && $log->auditable_type === \App\Models\TransmittalHeader::class)<a class="record-link" href="{{ route('transmittals.show', $log->auditable) }}">{{ $log->auditable->ref_no }}</a>@else<span class="muted">Record deleted</span>@endif</td></tr>@empty<tr><td colspan="6" class="empty">No audit activity found.</td></tr>@endforelse</tbody></table></div>
<div class="pager">
    <span>Showing {{ $logs->firstItem() ?: 0 }} to {{ $logs->lastItem() ?: 0 }} of {{ $logs->total() }} results</span>
    <div class="pager-controls" aria-label="Audit trail pagination">
        @if($logs->onFirstPage())
            <span class="disabled">&larr; Previous</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}">&larr; Previous</a>
        @endif

        @for($page = 1; $page <= $logs->lastPage(); $page++)
            @if($page === $logs->currentPage())
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $logs->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}">Next &rarr;</a>
        @else
            <span class="disabled">Next &rarr;</span>
        @endif
    </div>
</div>
@endsection
