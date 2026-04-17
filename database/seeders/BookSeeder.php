<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = Author::take(10)->get();
        $publishers = Publisher::take(10)->get();
        $genres = Genre::all();

        Book::factory()
            ->count(30)
            ->sequence(fn () => [
                'author_id' => $authors->random()->id,
                'publisher_id' => $publishers->random()->id,
            ])
            ->create()
            ->each(function (Book $book) use ($genres) {
                $book->genres()->attach(
                    $genres->random(rand(1, 3))->pluck('id')
                );
            });
    }
}
