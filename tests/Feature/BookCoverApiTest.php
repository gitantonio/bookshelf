<?php

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('uploads a valid cover image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $book = Book::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('cover.jpg', 400, 600);

    $this->actingAs($user)
        ->postJson("/api/books/{$book->id}/cover", [
            'cover' => $file,
        ])
        ->assertOk()
        ->assertJsonPath('data.id', $book->id);

    $book->refresh();
    expect($book->cover_path)->not->toBeNull();
    Storage::disk('public')->assertExists($book->cover_path);
});

it('rejects a non-image file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $book = Book::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs($user)
        ->postJson("/api/books/{$book->id}/cover", [
            'cover' => $file,
        ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'error' => [
                'details' => ['cover'],
            ],
        ]);
});

it('deletes a cover image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $book = Book::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('cover.jpg', 400, 600);
    $path = $file->store('covers', 'public');
    $book->update(['cover_path' => $path]);

    $this->actingAs($user)
        ->deleteJson("/api/books/{$book->id}/cover")
        ->assertNoContent();

    $book->refresh();
    expect($book->cover_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
