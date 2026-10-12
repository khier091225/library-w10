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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');
        $authorId = $request->query('author_id');

        $query = Book::with('author')
            ->withCount(['loans as active_loans_count' => fn ($q) => $q->whereNull('returned_at')]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhereHas('author', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status === 'available') {
            $query->where('is_reference', false)
                ->whereDoesntHave('loans', fn ($q) => $q->whereNull('returned_at'));
        } elseif ($status === 'borrowed') {
            $query->whereHas('loans', fn ($q) => $q->whereNull('returned_at'));
        } elseif ($status === 'reference') {
            $query->where('is_reference', true);
        }

        if (! empty($authorId)) {
            $query->where('author_id', $authorId);
        }

        $books = $query->orderBy('title')->paginate(12)->withQueryString();
        $authors = Author::orderBy('name')->get();

        return view('books.index', compact('books', 'authors', 'search', 'status', 'authorId'));
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

        $data = $request->validated();
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($data);

        return redirect()->route('books.show', $book)
            ->with('status', 'Book added successfully.');
    }

    /**
     * Display the specified book.
     */
    public function show(Request $request, Book $book): View
    {
        $book->load(['author', 'activeReservations.member']);

        $activeLoan = $book->loans()->whereNull('returned_at')->with('member')->latest('borrowed_at')->first();
        $hasActiveLoan = $activeLoan !== null;

        $user = $request->user();
        $isCurrentBorrower = ($user && $user->member && $activeLoan && $activeLoan->member_id === $user->member->id);

        $userReservation = ($user && $user->member)
            ? $book->activeReservations->firstWhere('member_id', $user->member->id)
            : null;

        $queuePosition = null;
        if ($userReservation) {
            $queuePosition = $book->activeReservations->search(fn ($r) => $r->id === $userReservation->id) + 1;
        }

        return view('books.show', compact('book', 'hasActiveLoan', 'activeLoan', 'isCurrentBorrower', 'userReservation', 'queuePosition'));
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

        $data = $request->validated();
        if ($request->hasFile('cover')) {
            if ($book->cover_path && Storage::disk('public')->exists($book->cover_path)) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

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
            'due_at' => now()->addDays(14),
            'returned_at' => null,
        ]);

        return redirect()->route('loans.my')
            ->with('status', "You have borrowed \"{$book->title}\".");
    }
}
