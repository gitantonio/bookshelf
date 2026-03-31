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
}
