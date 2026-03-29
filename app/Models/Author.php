<?php

namespace App\Models;

use App\Traits\HasDynamicIncludes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;
    use HasDynamicIncludes;

    protected $fillable = [
        'first_name',
        'last_name',
        'bio',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
