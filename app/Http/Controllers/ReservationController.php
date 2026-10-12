<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    /**
     * Place a hold on a book.
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        Gate::authorize('create', [Reservation::class, $book]);

        $user = $request->user();
        $member = $user->member ?? Member::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name, 'email' => $user->email]
        );

        Reservation::create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'reserved_at' => now(),
            'status' => 'pending',
        ]);

        return back()->with('status', "You have placed a hold on \"{$book->title}\".");
    }

    /**
     * Cancel an existing reservation.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        Gate::authorize('delete', $reservation);

        $reservation->update(['status' => 'cancelled']);

        return back()->with('status', 'Reservation cancelled.');
    }
}
