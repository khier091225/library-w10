<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Admin bypass.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Determine whether the user can reserve a book.
     */
    public function create(User $user, Book $book): bool
    {
        if ($book->is_reference) {
            return false;
        }

        // Cannot reserve a book that is currently available on the shelf
        if ($book->isAvailable()) {
            return false;
        }

        $member = $user->member;
        if ($member) {
            $alreadyHolding = $book->activeReservations()
                ->where('member_id', $member->id)
                ->exists();

            if ($alreadyHolding) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can cancel/delete the reservation.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasRole('librarian', 'admin')
            || ($user->member && $user->member->id === $reservation->member_id);
    }
}
