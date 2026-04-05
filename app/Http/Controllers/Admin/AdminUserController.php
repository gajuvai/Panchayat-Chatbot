<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('role')->withCount('complaints');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter = $request->input('role')) {
            $query->whereHas('role', fn($q) => $q->where('name', $roleFilter));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('role');
        $recentComplaints = $user->complaints()->latest()->limit(5)->get();
        $complaintsCount  = $user->complaints()->count();

        return view('admin.users.show', compact('user', 'recentComplaints', 'complaintsCount'));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('display_name')->get();
        $user->load('role');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update($data);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User role updated successfully.');
    }
}
