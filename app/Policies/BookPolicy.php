<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Admin bypass.
     * true = allow, false = deny, null = defer to specific ability method.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Don't auto-allow borrowing reference books or already-borrowed books even for admins
        if ($ability === 'borrow') {
            return null;
        }

        return $user->isAdmin() ? true : null;
    }

    /**
     * Determine whether anyone can view the list of books.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether anyone can view a specific book.
     */
    public function view(?User $user, Book $book): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create books.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('librarian', 'admin');
    }

    /**
     * Determine whether the user can update the book.
     */
    public function update(User $user, Book $book): bool
    {
        return $user->hasRole('librarian', 'admin');
    }

    /**
     * Determine whether the user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        // Librarians/admins only, and book cannot have active unreturned loans
        return $user->hasRole('librarian', 'admin')
            && $book->loans()->whereNull('returned_at')->doesntExist();
    }

    /**
     * Determine whether the user can borrow the book.
     */
    public function borrow(User $user, Book $book): bool
    {
        if ($book->is_reference) {
            return false;
        }

        return $book->loans()->whereNull('returned_at')->doesntExist();
    }
}
