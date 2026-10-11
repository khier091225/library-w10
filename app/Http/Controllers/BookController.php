<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    public function index(): View
    {
        $books = Book::with('author')
            ->withCount(['loans as active_loans_count' => fn ($query) => $query->whereNull('returned_at')])
            ->orderBy('title')
            ->paginate(12);

        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        Gate::authorize('create', Book::class);

        $authors = Author::orderBy('name')->get();
        $book = new Book;

        return view('books.form', compact('book', 'authors'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(BookRequest $request): RedirectResponse
    {
        Gate::authorize('create', Book::class);

        $book = Book::create($request->validated());

        return redirect()->route('books.show', $book)
            ->with('status', 'Book added successfully.');
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): View
    {
        $book->load('author');

        $hasActiveLoan = $book->loans()->whereNull('returned_at')->exists();

        return view('books.show', compact('book', 'hasActiveLoan'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book): View
    {
        Gate::authorize('update', $book);

        $authors = Author::orderBy('name')->get();

        return view('books.form', compact('book', 'authors'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        Gate::authorize('update', $book);

        $book->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('status', 'Book updated successfully.');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        Gate::authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('status', 'Book deleted successfully.');
    }

    /**
     * Borrow the specified book for the authenticated user's member account.
     */
    public function borrow(Request $request, Book $book): RedirectResponse
    {
        Gate::authorize('borrow', $book);

        $user = $request->user();

        // Get or create linked member record for this authenticated user
        $member = $user->member ?? Member::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name, 'email' => $user->email]
        );

        Loan::create([
            'book_id' => $book->id,
            'member_id' => $member->id,
            'borrowed_at' => now(),
            'returned_at' => null,
        ]);

        return redirect()->route('loans.my')
            ->with('status', "You have borrowed \"{$book->title}\".");
    }
}
