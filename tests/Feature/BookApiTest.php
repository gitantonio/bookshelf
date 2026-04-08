<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a paginated list of books', function () {
    Book::factory()->count(20)->create();

    $response = $this->getJson('/api/books');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'isbn', 'publication_year'],
            ],
            'meta' => ['current_page', 'total', 'per_page'],
            'links',
        ]);

    expect($response->json('meta.total'))->toEqual(20);
});

it('returns a single book', function () {
    $book = Book::factory()->create([
        'title' => 'Test Book',
    ]);

    $this->getJson("/api/books/{$book->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Test Book')
        ->assertJsonPath('data.id', $book->id);
});

it('returns 404 for a non-existent book', function () {
    $this->getJson('/api/books/999')
        ->assertNotFound();
});

it('creates a book', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();

    $data = [
        'title' => 'New Book',
        'isbn' => '9780306406157',
        'publication_year' => 2026,
        'author_id' => $author->id,
    ];

    $this->actingAs($user)
        ->postJson('/api/books', $data)
        ->assertCreated()
        ->assertJsonPath('data.title', 'New Book');

    $this->assertDatabaseHas('books', [
        'title' => 'New Book',
        'user_id' => $user->id,
    ]);
});

it('validates required fields on creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/books', [])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'error' => [
                'details' => [
                    'title', 'isbn', 'publication_year',
                ],
            ],
        ]);
});

it('prevents unauthenticated users from creating books', function () {
    $this->postJson('/api/books', [
        'title' => 'Should Fail',
    ])->assertUnauthorized();
});

it('updates a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'user_id' => $user->id
    ]);

    $this->actingAs($user)
        ->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');
});

it('prevents updating another user\'s book', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $book = Book::factory()->create([
        'user_id' => $owner->id
    ]);

    $this->actingAs($other)
        ->putJson("/api/books/{$book->id}", [
            'title' => 'Stolen',
        ])
        ->assertForbidden();
});

it('deletes a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'user_id' => $user->id
    ]);

    $this->actingAs($user)
        ->deleteJson("/api/books/{$book->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});
