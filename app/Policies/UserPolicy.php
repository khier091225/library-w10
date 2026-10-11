<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view users list.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    /**
     * Determine whether the user can update another user's role.
     * Admins only, and an admin cannot demote themselves.
     */
    public function updateRole(User $authUser, User $targetUser, ?string $newRole = null): bool
    {
        if (! $authUser->isAdmin()) {
            return false;
        }

        // Prevent self-demotion
        if ($authUser->id === $targetUser->id && $newRole !== null && $newRole !== 'admin') {
            return false;
        }

        return true;
    }
}
