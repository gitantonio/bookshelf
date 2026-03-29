<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Author;

class AuthorBookController extends Controller
{
    public function index(Author $author)
    {
        $books = $author->books()->paginate(15);

        return BookResource::collection($books);
    }
}
