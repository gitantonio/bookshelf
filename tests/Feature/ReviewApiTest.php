<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a review', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $this->actingAs($user)
        ->postJson("/api/books/{$book->id}/reviews", [
            'rating' => 4,
            'body' => 'Great book!',
        ])
        ->assertCreated()
        ->assertJsonPath('data.rating', 4);

    expect($book->fresh()->average_rating)->toEqual(4.00);
    expect($book->fresh()->reviews_count)->toEqual(1);
});

it('prevents duplicate reviews', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->postJson("/api/books/{$book->id}/reviews", [
            'rating' => 5,
        ])
        ->assertStatus(409)
        ->assertJsonPath(
            'error.message',
            'You have already reviewed this book.'
        );
});

it('only allows the author to delete a review', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $book = Book::factory()->create();
    $review = Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $author->id,
    ]);

    $this->actingAs($other)
        ->deleteJson(
            "/api/books/{$book->id}/reviews/{$review->id}"
        )
        ->assertForbidden();

    $this->actingAs($author)
        ->deleteJson(
            "/api/books/{$book->id}/reviews/{$review->id}"
        )
        ->assertNoContent();
});
