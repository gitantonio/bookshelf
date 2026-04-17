<?php

use App\Models\Book;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a paginated list of publishers', function () {
    Publisher::factory()->count(20)->create();

    $response = $this->getJson('/api/publishers');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'country'],
            ],
            'meta' => ['current_page', 'total', 'per_page'],
            'links',
        ]);

    expect($response->json('meta.total'))->toEqual(20);
});

it('returns a single publisher', function () {
    $publisher = Publisher::factory()->create([
        'name' => 'Mondadori',
    ]);

    $this->getJson("/api/publishers/{$publisher->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Mondadori')
        ->assertJsonPath('data.id', $publisher->id);
});

it('returns 404 for a non-existent publisher', function () {
    $this->getJson('/api/publishers/999')
        ->assertNotFound();
});

it('creates a publisher', function () {
    $user = User::factory()->create();

    $data = [
        'name' => 'Adelphi',
        'country' => 'Italy',
        'website' => 'https://www.adelphi.it',
    ];

    $this->actingAs($user)
        ->postJson('/api/publishers', $data)
        ->assertCreated()
        ->assertJsonPath('data.name', 'Adelphi');

    $this->assertDatabaseHas('publishers', [
        'name' => 'Adelphi',
        'country' => 'Italy',
    ]);
});

it('validates required fields on creation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/publishers', [])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'error' => [
                'details' => [
                    'name', 'country',
                ],
            ],
        ]);
});

it('validates website is a valid url', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/publishers', [
            'name' => 'Test',
            'country' => 'Italy',
            'website' => 'not-a-url',
        ])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'error' => [
                'details' => ['website'],
            ],
        ]);
});

it('prevents unauthenticated users from creating publishers', function () {
    $this->postJson('/api/publishers', [
        'name' => 'Should Fail',
    ])->assertUnauthorized();
});

it('updates a publisher', function () {
    $user = User::factory()->create();
    $publisher = Publisher::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/publishers/{$publisher->id}", [
            'name' => 'Updated Name',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('deletes a publisher', function () {
    $user = User::factory()->create();
    $publisher = Publisher::factory()->create();

    $this->actingAs($user)
        ->deleteJson("/api/publishers/{$publisher->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('publishers', ['id' => $publisher->id]);
});

it('prevents deleting a publisher that has books', function () {
    $user = User::factory()->create();
    $publisher = Publisher::factory()->create();
    Book::factory()->create(['publisher_id' => $publisher->id]);

    $this->actingAs($user)
        ->deleteJson("/api/publishers/{$publisher->id}")
        ->assertStatus(409)
        ->assertJsonPath(
            'error.message',
            'Cannot delete a publisher that has books.'
        );
});
