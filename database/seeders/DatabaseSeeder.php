<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed one user per role (password: 'password')
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $librarian = User::factory()->librarian()->create([
            'name' => 'Librarian User',
            'email' => 'librarian@example.com',
            'password' => Hash::make('password'),
        ]);

        $memberUser = User::factory()->member()->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
        ]);

        // Link the member user to a members row
        $primaryMember = Member::create([
            'user_id' => $memberUser->id,
            'name' => $memberUser->name,
            'email' => $memberUser->email,
        ]);

        // 2. Seed 10 authors x 4 books (40 books total)
        $authors = Author::factory(10)->create();
        $books = collect();

        foreach ($authors as $index => $author) {
            // Create 3 standard books and 1 reference book per 2 authors
            $isRef = ($index % 2 === 0);
            $createdBooks = Book::factory(3)->for($author)->create();
            $refBook = Book::factory()->reference()->for($author)->create();
            $books = $books->merge($createdBooks)->push($refBook);
        }

        // 3. Seed remaining members to reach 30 total members
        $otherMembers = Member::factory(29)->create();
        $allMembers = collect([$primaryMember])->merge($otherMembers);

        // Filter non-reference books for loans
        $borrowableBooks = $books->where('is_reference', false)->values();
        $currentlyLoanedBookIds = [];

        // 4. Seed 1-3 loans per member, with about 20% still unreturned
        foreach ($allMembers as $member) {
            $loanCount = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $loanCount; $i++) {
                // Determine if this loan is unreturned (~20% probability)
                $isUnreturned = fake()->boolean(20);

                if ($isUnreturned) {
                    // Pick a book that is not already actively loaned out
                    $availableBook = $borrowableBooks->first(fn ($b) => ! in_array($b->id, $currentlyLoanedBookIds, true));
                    if ($availableBook) {
                        $currentlyLoanedBookIds[] = $availableBook->id;
                        $borrowedAt = fake()->dateTimeBetween('-25 days', 'now');
                        $dueAt = (clone $borrowedAt)->modify('+14 days');

                        Loan::create([
                            'book_id' => $availableBook->id,
                            'member_id' => $member->id,
                            'borrowed_at' => $borrowedAt,
                            'due_at' => $dueAt,
                            'returned_at' => null,
                        ]);

                        continue;
                    }
                }

                // Otherwise, a returned loan
                $book = $borrowableBooks->random();
                $borrowedAt = fake()->dateTimeBetween('-60 days', '-10 days');
                $dueAt = (clone $borrowedAt)->modify('+14 days');
                $returnedAt = fake()->dateTimeBetween($borrowedAt, 'now');

                Loan::create([
                    'book_id' => $book->id,
                    'member_id' => $member->id,
                    'borrowed_at' => $borrowedAt,
                    'due_at' => $dueAt,
                    'returned_at' => $returnedAt,
                ]);
            }
        }

        // Seed sample pending holds/reservations on actively loaned books
        $activeLoans = Loan::whereNull('returned_at')->get();
        foreach ($activeLoans->take(6) as $loan) {
            $otherMember = $allMembers->first(fn ($m) => $m->id !== $loan->member_id);
            if ($otherMember) {
                Reservation::create([
                    'book_id' => $loan->book_id,
                    'member_id' => $otherMember->id,
                    'reserved_at' => now()->subDays(rand(1, 4)),
                    'status' => 'pending',
                ]);
            }
        }
    }
}
