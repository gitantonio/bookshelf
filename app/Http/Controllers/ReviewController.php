<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Book $book)
    {
        $this->authorize('viewAny', Review::class);

        $reviews = $book->reviews()
            ->with('user')
            ->latest()
            ->paginate(15);

        return ReviewResource::collection($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Book $book)
    {
        $this->authorize('create', Review::class);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $exists = $book->reviews()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            throw new BusinessException(
                'You have already reviewed this book.'
            );
        }

        $review = DB::transaction(function () use ($book, $validated, $request) {
            $review = $book->reviews()->make($validated);
            $review->user_id = $request->user()->id;
            $review->save();

            $book->updateRatingStats();

            return $review;
        });

        $review->load('user');

        return (new ReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book, Review $review)
    {
        $this->authorize('view', $review);

        $review->load('user');

        return new ReviewResource($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book, Review $review)
    {
        $this->authorize('update', $review);
        $this->ensureEditWindow($review);

        $validated = $request->validate([
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($review, $validated, $book) {
            $review->update($validated);
            $book->updateRatingStats();
        });

        $review->load('user');

        return new ReviewResource($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Book $book, Review $review)
    {
        $this->authorize('delete', $review);
        $this->ensureEditWindow($review);

        DB::transaction(function () use ($review, $book) {
            $review->delete();
            $book->updateRatingStats();
        });

        return response()->json(null, 204);
    }

    private function ensureEditWindow(Review $review): void
    {
        $window = config('bookshelf.review_edit_window_minutes');

        if ($review->created_at->diffInMinutes(now()) > $window) {
            throw new BusinessException(
                "Reviews can only be modified within {$window} minutes of creation."
            );
        }
    }
}
