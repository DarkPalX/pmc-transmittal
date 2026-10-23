<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $this->authorizeSuperadmin();
        return view('users.index', ['users' => User::query()->latest()->paginate(15)]);
    }

    public function create(): View
    {
        $this->authorizeSuperadmin();
        return view('users.form', ['user' => new User(['role' => 'user'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin();
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->authorizeSuperadmin();
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeSuperadmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:user,superadmin'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        if (filled($data['password'] ?? null)) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeSuperadmin();
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    private function authorizeSuperadmin(): void
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:user,superadmin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
