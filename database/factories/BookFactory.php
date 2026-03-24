<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(rand(2, 6)),
            'isbn' => fake()->isbn13(),
            'description' => fake()->paragraphs(2, true),
            'publication_year' => fake()->numberBetween(1950, 2026),
            'language' => fake()->randomElement(['en', 'it', 'fr', 'es', 'de']),
            'pages' => fake()->numberBetween(80, 800),
        ];
    }
}
