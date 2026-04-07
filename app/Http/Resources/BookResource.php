<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->when(
                $request->routeIs('books.show'),
                $this->description
            ),
            'publication_year' => $this->publication_year,
            'language' => $this->language,
            'pages' => $this->pages,
            'is_recent' => ($this->publication_year >= now()->year - 2),

            'author' => new AuthorResource(
                $this->whenLoaded('author')
            ),
            'genres' => GenreResource::collection(
                $this->whenLoaded('genres')
            ),

            'cover_url' => ($this->cover_path)
                ? Storage::disk(config('bookshelf.images_disk', 'public'))
                    ->url($this->cover_path)
                : null,

            'average_rating' => round($this->average_rating, 2),
            'reviews_count' => $this->reviews_count,

            'created_at' => $this->created_at->toIso8601ZuluString(),
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
        ];
    }
}
