<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
