<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Reviews
 */
class ReviewController extends Controller
{
    /**
     * List reviews for a book
     *
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     *
     * @apiResourceCollection App\Http\Resources\ReviewResource
     * @apiResourceModel App\Models\Review paginate=15 with=user
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Book]."}
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
     * Create a review
     *
     * A user can only leave one review per book.
     *
     * @authenticated
     *
     * @bodyParam rating integer required Rating between 1 and 5. Example: 5
     * @bodyParam body string Review text (max 2000). Example: An absolute masterpiece.
     *
     * @apiResource status=201 App\Http\Resources\ReviewResource
     * @apiResourceModel App\Models\Review with=user
     *
     * @response 422 scenario="Already reviewed" {"message":"You have already reviewed this book."}
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
     * Show a review
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\ReviewResource
     * @apiResourceModel App\Models\Review with=user
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Review]."}
     */
    public function show(Book $book, Review $review)
    {
        $this->authorize('view', $review);

        $review->load('user');

        return new ReviewResource($review);
    }

    /**
     * Update a review
     *
     * Only editable within `bookshelf.review_edit_window_minutes` from creation.
     *
     * @authenticated
     *
     * @bodyParam rating integer Rating between 1 and 5. Example: 4
     * @bodyParam body string Review text (max 2000). Example: On reflection, I'd knock off one star.
     *
     * @apiResource App\Http\Resources\ReviewResource
     * @apiResourceModel App\Models\Review with=user
     *
     * @response 422 scenario="Edit window expired" {"message":"Reviews can only be modified within 60 minutes of creation."}
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
     * Delete a review
     *
     * Subject to the same edit window as updates.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 422 scenario="Edit window expired" {"message":"Reviews can only be modified within 60 minutes of creation."}
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
