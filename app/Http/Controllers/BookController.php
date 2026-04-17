<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * @group Books
 */
class BookController extends Controller
{
    /**
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     * @queryParam sort string Sort field (prefix with `-` for descending). Example: -publication_year
     * @queryParam include string Related resources to include (author, genres). Example: author,genres
     * @queryParam language string Filter by language code. Example: en
     * @queryParam year_from integer Minimum publication year. Example: 2000
     * @queryParam year_to integer Maximum publication year. Example: 2026
     * @queryParam author_id integer Filter by author ID. Example: 3
     * @queryParam genre string Filter by genre slug. Example: fantasy
     * @queryParam search string Search in title and description. Example: rose
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);

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
     * @authenticated
     */
    public function store(StoreBookRequest $request)
    {
        $this->authorize('create', Book::class);

        $book = $request->user()->books()->create(
            $request->validated()
        );

        if ($request->has('genre_ids')) {
            $book->genres()->sync($request->genre_ids);
        }

        $book->load(['author', 'publisher', 'genres']);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @unauthenticated
     *
     * @queryParam include string Related resources to include (author, genres). Example: author,genres
     */
    public function show(Book $book)
    {
        $this->authorize('view', $book);

        $book->loadIncludes(['author', 'publisher', 'genres']);

        return new BookResource($book);
    }

    /**
     * @authenticated
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        return new BookResource($book);
    }

    /**
     * @authenticated
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
