<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $borrowedAt = fake()->dateTimeBetween('-40 days', '-5 days');
        $dueAt = (clone $borrowedAt)->modify('+14 days');
        $returnedAt = fake()->dateTimeBetween($borrowedAt, 'now');

        return [
            'book_id' => Book::factory(),
            'member_id' => Member::factory(),
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'returned_at' => $returnedAt,
        ];
    }

    public function unreturned(): static
    {
        return $this->state(fn () => [
            'returned_at' => null,
        ]);
    }

    public function overdue(): static
    {
        $borrowedAt = now()->subDays(25);

        return $this->state(fn () => [
            'borrowed_at' => $borrowedAt,
            'due_at' => now()->subDays(11),
            'returned_at' => null,
        ]);
    }
}
