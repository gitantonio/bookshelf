<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Support\Facades\Cache;

/**
 * @group Genres
 */
class GenreController extends Controller
{
    /**
     * @unauthenticated
     */
    public function index()
    {
        $genres = Cache::remember('genres.all', 3600, function () {
            return Genre::orderBy('name')->get()->toArray();
        });

        return GenreResource::collection(
            Genre::hydrate($genres)
        );
    }
}
