<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Display loans belonging to the authenticated member (/my/loans).
     */
    public function myLoans(Request $request): View
    {
        $user = $request->user();
        $member = $user->member;

        $loans = $member
            ? $member->loans()->with('book.author')->latest('borrowed_at')->paginate(10)
            : collect();

        return view('loans.my', compact('loans'));
    }

    /**
     * Display all loans for the circulation desk (/desk).
     * Accessible by librarians and admins.
     */
    public function desk(Request $request): View
    {
        Gate::authorize('viewAny', Loan::class);

        $filter = $request->query('status', 'active');

        $query = Loan::with(['book.author', 'member'])->latest('borrowed_at');

        if ($filter === 'active') {
            $query->whereNull('returned_at');
        } elseif ($filter === 'returned') {
            $query->whereNotNull('returned_at');
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('loans.desk', compact('loans', 'filter'));
    }

    /**
     * Mark a loan as returned.
     * Only librarians and admins may mark a loan returned.
     */
    public function markReturned(Loan $loan): RedirectResponse
    {
        Gate::authorize('return', $loan);

        if (! $loan->returned_at) {
            $loan->update(['returned_at' => now()]);
        }

        return back()->with('status', "Book \"{$loan->book->title}\" marked as returned.");
    }
}
