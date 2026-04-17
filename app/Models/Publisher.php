<?php

namespace App\Models;

use App\Traits\HasDynamicIncludes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    /** @use HasFactory<\Database\Factories\PublisherFactory> */
    use HasFactory;
    use HasDynamicIncludes;

    protected $fillable = [
        'name',
        'country',
        'website',
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
