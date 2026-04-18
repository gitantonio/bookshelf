<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;

/**
 * @group Authors
 */
class AuthorController extends Controller
{
    /**
     * List authors
     *
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     *
     * @apiResourceCollection App\Http\Resources\AuthorResource
     * @apiResourceModel App\Models\Author paginate=15
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function index()
    {
        $this->authorize('viewAny', Author::class);

        $authors = Author::paginate(15);

        return AuthorResource::collection($authors);
    }

    /**
     * Create an author
     *
     * @authenticated
     *
     * @bodyParam first_name string required First name (max 255). Example: Umberto
     * @bodyParam last_name string required Last name (max 255). Example: Eco
     * @bodyParam bio string Biography (max 5000). Example: Italian medievalist, philosopher and novelist.
     *
     * @apiResource status=201 App\Http\Resources\AuthorResource
     * @apiResourceModel App\Models\Author
     *
     * @response 422 scenario="Validation failed" {"message":"The first name field is required.","errors":{"first_name":["The first name field is required."]}}
     */
    public function store(Request $request)
    {
        $this->authorize('create', Author::class);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);

        $author = Author::create($validated);

        return (new AuthorResource($author))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show an author
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\AuthorResource
     * @apiResourceModel App\Models\Author
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Author]."}
     */
    public function show(Author $author)
    {
        $this->authorize('view', $author);

        return new AuthorResource($author);
    }

    /**
     * Update an author
     *
     * @authenticated
     *
     * @bodyParam first_name string First name (max 255). Example: Umberto
     * @bodyParam last_name string Last name (max 255). Example: Eco
     * @bodyParam bio string Biography (max 5000). Example: Updated bio.
     *
     * @apiResource App\Http\Resources\AuthorResource
     * @apiResourceModel App\Models\Author
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(Request $request, Author $author)
    {
        $this->authorize('update', $author);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);

        $author->update($validated);

        return new AuthorResource($author);
    }

    /**
     * Delete an author
     *
     * Fails with 422 if the author still has books.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 422 scenario="Author has books" {"message":"Cannot delete an author that has books."}
     */
    public function destroy(Author $author)
    {
        $this->authorize('delete', $author);

        if ($author->books()->exists()) {
            throw new BusinessException(
                'Cannot delete an author that has books.'
            );
        }

        $author->delete();

        return response()->json(null, 204);
    }
}
