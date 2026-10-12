<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

// Home redirects to books catalog
Route::get('/', fn () => redirect()->route('books.index'));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My loans for members
    Route::get('/my/loans', [LoanController::class, 'myLoans'])->name('loans.my');

    // Borrow a book
    Route::post('/books/{book}/borrow', [BookController::class, 'borrow'])->name('books.borrow');

    // Book Reservations / Holds
    Route::post('/books/{book}/reserve', [ReservationController::class, 'store'])->name('books.reserve');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    // Book management (create, store, edit, update, destroy)
    Route::resource('books', BookController::class)->except(['index', 'show']);
});

// Public: index and show. Kept AFTER resource except(['index', 'show'])
Route::resource('books', BookController::class)->only(['index', 'show']);

// Circulation Desk: Librarians and Admins
Route::middleware(['auth', 'role:librarian,admin'])->prefix('desk')->name('desk.')->group(function () {
    Route::get('/', [LoanController::class, 'desk'])->name('index');
    Route::post('/loans/{loan}/return', [LoanController::class, 'markReturned'])->name('loans.return');
});

// Admin area: Admins only
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
});

require __DIR__.'/auth.php';
