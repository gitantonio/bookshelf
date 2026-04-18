<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Books
 */
class BookCoverController extends Controller
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk(
            config('bookshelf.images_disk', 'public')
        );
    }

    /**
     * Upload a book cover
     *
     * Replaces the existing cover if one is present.
     *
     * @authenticated
     *
     * @bodyParam cover file required Image file (jpg, jpeg, png or webp). Max 2MB, min 200x300px.
     *
     * @apiResource App\Http\Resources\BookResource
     * @apiResourceModel App\Models\Book with=author,genres
     *
     * @response 422 scenario="Invalid image" {"message":"The cover must be an image.","errors":{"cover":["The cover must be an image."]}}
     */
    public function store(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        $request->validate([
            'cover' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=200,min_height=300',
            ],
        ]);

        if ($book->cover_path) {
            $this->disk->delete($book->cover_path);
        }

        $path = $request->file('cover')->store(
            'covers',
            config('bookshelf.images_disk', 'public')
        );

        $book->update(['cover_path' => $path]);
        $book->load(['author', 'genres']);

        return new BookResource($book);
    }

    /**
     * Delete a book cover
     *
     * Returns 204 even if no cover was set.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        if ($book->cover_path) {
            $this->disk->delete($book->cover_path);
            $book->update(['cover_path' => null]);
        }

        return response()->json(null, 204);
    }
}
