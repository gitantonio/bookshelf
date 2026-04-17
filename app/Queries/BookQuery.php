<?php

namespace App\Queries;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class BookQuery
{
    public function __construct(
        private readonly Request $request,
    ) {}

    public function paginate(): LengthAwarePaginator
    {
        return Book::query()
            ->withIncludes(['author', 'publisher', 'genres'])
            ->withSorting([
                'title', 'publication_year', 'created_at', 'pages',
            ])
            ->when($this->request->language, fn ($q, $v) =>
                $q->where('language', $v)
            )
            ->when($this->request->year_from, fn ($q, $v) =>
                $q->where('publication_year', '>=', (int) $v)
            )
            ->when($this->request->year_to, fn ($q, $v) =>
                $q->where('publication_year', '<=', (int) $v)
            )
            ->when($this->request->author_id, fn ($q, $v) =>
                $q->where('author_id', (int) $v)
            )
            ->when($this->request->genre, fn ($q, $v) =>
                $q->whereHas('genres', fn ($g) =>
                    $g->where('slug', $v)
                )
            )
            ->when($this->request->search, fn ($q, $v) =>
                $q->where(fn ($sub) =>
                    $sub->where('title', 'like', "%{$v}%")
                        ->orWhere('description', 'like', "%{$v}%")
                )
            )
            ->paginate($this->perPage())
            ->withQueryString();
    }

    private function perPage(): int
    {
        return min((int) $this->request->query('per_page', 15), 100);
    }
}
