<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $books->each(function (Book $book) use ($users) {
            $reviewers = $users->random(
                min(rand(0, 5), $users->count())
            );

            foreach ($reviewers as $user) {
                Review::factory()
                    ->for($book)
                    ->for($user)
                    ->create();
            }

            $book->updateRatingStats();
        });
    }
}
