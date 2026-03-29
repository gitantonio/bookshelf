<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Fantasy', 'Science Fiction', 'Mystery', 'Thriller',
            'Romance', 'Horror', 'Historical Fiction', 'Literary Fiction',
            'Biography', 'Self-Help', 'Science', 'Technology',
            'Philosophy', 'Poetry', 'Travel',
        ];

        foreach ($genres as $name) {
            Genre::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
