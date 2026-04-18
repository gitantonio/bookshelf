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
     * List an author's books
     *
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     *
     * @apiResourceCollection App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book paginate=15 with=author,genres
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Author]."}
     */
    public function index(Author $author)
    {
        $books = $author->books()->paginate(15);

        return BookResource::collection($books);
    }
}
