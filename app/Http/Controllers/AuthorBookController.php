<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Author;

/**
 * @group Authors
 */
class AuthorBookController extends Controller
{
    /**
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     */
    public function index(Author $author)
    {
        $books = $author->books()->paginate(15);

        return BookResource::collection($books);
    }
}
