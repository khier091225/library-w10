<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'isbn' => fake()->unique()->numerify('978##########'),
            'title' => ucwords(fake()->words(fake()->numberBetween(2, 5), true)),
            'published_year' => fake()->numberBetween(1970, 2024),
            'is_reference' => false,
            'cover_path' => null,
        ];
    }

    public function reference(): static
    {
        return $this->state(fn () => [
            'is_reference' => true,
        ]);
    }
}
