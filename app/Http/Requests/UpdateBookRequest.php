<?php

namespace App\Http\Requests;

use App\Rules\ValidIsbn13;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'isbn' => [
                'sometimes',
                'string',
                new ValidIsbn13(),
                Rule::unique('books')->ignore($this->route('book')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'publication_year' => ['sometimes', 'integer', 'min:1450', 'max:' . (now()->year + 1)],
            'language' => ['sometimes', 'string', 'size:2'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10000'],

            'author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }
}
