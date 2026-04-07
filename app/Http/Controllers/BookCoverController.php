<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookCoverController extends Controller
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk(
            config('bookshelf.images_disk', 'public')
        );
    }

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
            'public'
        );

        $book->update(['cover_path' => $path]);
        $book->load(['author', 'genres']);

        return new BookResource($book);
    }

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
