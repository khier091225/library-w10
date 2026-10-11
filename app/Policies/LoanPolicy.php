<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Admin bypass.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Determine whether the user can view all loans (circulation desk).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('librarian', 'admin');
    }

    /**
     * Determine whether the user can view the specific loan.
     * A member may view only their own loans; librarians view all.
     */
    public function view(User $user, Loan $loan): bool
    {
        return $user->hasRole('librarian', 'admin')
            || ($user->member && $user->member->id === $loan->member_id);
    }

    /**
     * Determine whether the user can mark a loan as returned.
     * Only librarians (and admins) mark a loan returned.
     */
    public function return(User $user, Loan $loan): bool
    {
        return $user->hasRole('librarian', 'admin');
    }
}
