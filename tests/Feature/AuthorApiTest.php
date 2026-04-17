<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates an author', function () {
    $user = User::factory()->create();

    $data = [
        'first_name' => 'Italo',
        'last_name' => 'Calvino',
        'bio' => 'Italian journalist and writer.',
    ];

    $this->actingAs($user)
        ->postJson('/api/authors', $data)
        ->assertCreated()
        ->assertJsonPath('data.first_name', 'Italo')
        ->assertJsonPath('data.last_name', 'Calvino');

    $this->assertDatabaseHas('authors', [
        'first_name' => 'Italo',
        'last_name' => 'Calvino',
    ]);
});

it('prevents deleting an author that has books', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    Book::factory()->create(['author_id' => $author->id]);

    $this->actingAs($user)
        ->deleteJson("/api/authors/{$author->id}")
        ->assertStatus(409)
        ->assertJsonPath(
            'error.message',
            'Cannot delete an author that has books.'
        );
});

it('partially updates an author', function () {
    $user = User::factory()->create();
    $author = Author::factory()->create([
        'first_name' => 'Italo',
        'last_name' => 'Calvino',
    ]);

    $this->actingAs($user)
        ->putJson("/api/authors/{$author->id}", [
            'last_name' => 'Svevo',
        ])
        ->assertOk()
        ->assertJsonPath('data.last_name', 'Svevo')
        ->assertJsonPath('data.first_name', 'Italo');
});
