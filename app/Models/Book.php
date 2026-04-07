<?php

namespace App\Models;

use App\Traits\HasDynamicIncludes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;
    use HasDynamicIncludes;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'publication_year',
        'language',
        'pages',
        'author_id',
    ];

    protected $attributes = [
        'language' => 'en',
        'average_rating' => 0,
        'reviews_count' => 0,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function updateRatingStats(): void
    {
        $this->average_rating = $this->reviews()->avg('rating') ?? 0;
        $this->reviews_count = $this->reviews()->count();
        $this->save();
    }
}
