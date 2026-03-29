<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min($perPage, 100);

        $books = Book::query()
            ->withIncludes(['author', 'genres'])
            ->withSorting([
                'title', 'publication_year', 'created_at', 'pages'
            ])
            ->when($request->language, fn ($q, $v) =>
                $q->where('language', $v)
            )
            ->when($request->year_from, fn ($q, $v) =>
                $q->where('publication_year', '>=', (int) $v)
            )
            ->when($request->year_to, fn ($q, $v) =>
                $q->where('publication_year', '<=', (int) $v)
            )
            ->when($request->author_id, fn ($q, $v) =>
                $q->where('author_id', (int) $v)
            )
            ->when($request->genre, fn ($q, $v) =>
                $q->whereHas('genres', fn ($g) =>
                    $g->where('slug', $v)
                )
            )
            ->when($request->search, fn ($q, $v) =>
                $q->where(fn ($sub) =>
                    $sub->where('title', 'like', "%{$v}%")
                        ->orWhere('description', 'like', "%{$v}%")
                )
            )
            ->paginate($perPage)
            ->withQueryString();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());

        if ($request->has('genre_ids')) {
            $book->genres()->sync($request->genre_ids);
        }

        $book->load(['author', 'genres']);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->loadIncludes(['author', 'genres']);

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());

        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
