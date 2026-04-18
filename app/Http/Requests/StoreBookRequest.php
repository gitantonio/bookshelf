<?php

namespace App\Http\Requests;

use App\Rules\ValidIsbn13;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' =>       ['required', 'string', 'max:255'],
            'isbn' =>        ['required', 'string', new ValidIsbn13(), 'unique:books'],
            'description' => ['nullable', 'string', 'max:5000'],
            'publication_year' => ['required', 'integer', 'min:1450', 'max:' . (now()->year + 1)],
            'language' =>    ['sometimes', 'string', 'size:2'],
            'pages' =>       ['nullable', 'integer', 'min:1', 'max:10000'],

            'author_id' =>   ['nullable', 'integer', 'exists:authors,id'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'genre_ids' =>   ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title' =>       ['description' => "The book's title.",                       'example' => 'The Name of the Rose'],
            'isbn' =>        ['description' => 'A valid, unique ISBN-13.',                'example' => '9780156001311'],
            'description' => ['description' => 'Free-form description (max 5000 chars).', 'example' => 'A historical murder mystery set in a medieval abbey.'],
            'publication_year' => ['description' => 'Year of publication.',               'example' => 1980],
            'language' =>    ['description' => 'Two-letter ISO language code.',           'example' => 'en'],
            'pages' =>       ['description' => 'Number of pages.',                        'example' => 512],
            'author_id' =>   ['description' => "ID of the book's author.",                'example' => 3],
            'publisher_id' => ['description' => "ID of the book's publisher.",            'example' => 7],
            'genre_ids' =>   ['description' => 'Array of genre IDs to attach.',           'example' => [1, 4]],
        ];
    }
}
