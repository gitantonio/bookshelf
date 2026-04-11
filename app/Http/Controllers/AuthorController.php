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
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     */
    public function index()
    {
        $this->authorize('viewAny', Author::class);

        $authors = Author::paginate(15);

        return AuthorResource::collection($authors);
    }

    /**
     * @authenticated
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
     * @unauthenticated
     */
    public function show(Author $author)
    {
        $this->authorize('view', $author);

        return new AuthorResource($author);
    }

    /**
     * @authenticated
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
     * @authenticated
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
