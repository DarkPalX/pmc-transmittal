@extends('layouts.app')
@section('title', 'User management')
@section('content')
<div class="page-head"><div><span class="eyebrow">SYSTEM / USERS</span><h1>User management</h1><p class="muted">Create and manage workspace accounts.</p></div><a class="button primary" href="{{ route('users.create') }}">+ Add user</a></div>
<div class="table-card"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead><tbody>@forelse($users as $user)<tr><td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}</td><td><span class="status {{ $user->isSuperadmin() ? 'transmitted' : 'saved' }}">{{ $user->role === 'superadmin' ? 'Superadmin' : 'User' }}</span></td><td>{{ $user->created_at?->format('d M Y') }}</td><td class="row-actions"><a class="button ghost small" href="{{ route('users.edit', $user) }}">Edit</a>@if(!$user->is(auth()->user()))<form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button class="danger-link">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="empty">No users found.</td></tr>@endforelse</tbody></table></div>
<div class="pager"><span>Showing {{ $users->firstItem() ?: 0 }} to {{ $users->lastItem() ?: 0 }} of {{ $users->total() }} users</span><div class="pager-controls">{{ $users->links() }}</div></div>
@endsection
