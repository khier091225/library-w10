<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users for administrators.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('member.loans')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Update a user's role with self-demotion prevention.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['member', 'librarian', 'admin'])],
        ]);

        Gate::authorize('updateRole', [$user, $validated['role']]);

        $user->role = $validated['role'];
        $user->save();

        return back()->with('status', "Role updated to {$user->role} for {$user->name}.");
    }
}
