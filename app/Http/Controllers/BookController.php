<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Queries\BookQuery;
use Illuminate\Http\Request;

/**
 * @group Books
 */
class BookController extends Controller
{
    /**
     * List books
     *
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     * @queryParam sort string Sort field (prefix with `-` for descending). Allowed: title, publication_year, created_at, pages. Example: -publication_year
     * @queryParam include string Comma-separated related resources (author, publisher, genres). Example: author,genres
     * @queryParam language string Filter by ISO 2-letter language code. Example: en
     * @queryParam year_from integer Minimum publication year. Example: 2000
     * @queryParam year_to integer Maximum publication year. Example: 2026
     * @queryParam author_id integer Filter by author ID. Example: 3
     * @queryParam genre string Filter by genre slug. Example: fantasy
     * @queryParam search string Search in title and description. Example: rose
     *
     * @apiResourceCollection App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book paginate=15 with=author,publisher,genres
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);

        $books = (new BookQuery($request))->paginate();

        return BookResource::collection($books);
    }

    /**
     * Create a book
     *
     * @authenticated
     *
     * @apiResource status=201 App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book with=author,publisher,genres
     *
     * @response 422 scenario="Validation failed" {"message":"The isbn has already been taken.","errors":{"isbn":["The isbn has already been taken."]}}
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
     * Show a book
     *
     * @unauthenticated
     *
     * @queryParam include string Comma-separated related resources (author, publisher, genres). Example: author,genres
     *
     * @apiResource App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book with=author,publisher,genres
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Book]."}
     */
    public function show(Book $book)
    {
        $this->authorize('view', $book);

        $book->loadIncludes(['author', 'publisher', 'genres']);

        return new BookResource($book);
    }

    /**
     * Update a book
     *
     * @authenticated
     *
     * @apiResource App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book with=author,publisher,genres
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update($request->validated());

        if ($request->has('genre_ids')) {
            $book->genres()->sync($request->genre_ids);
        }

        $book->load(['author', 'publisher', 'genres']);

        return new BookResource($book);
    }

    /**
     * Delete a book
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
