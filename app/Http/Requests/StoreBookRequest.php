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
            'publication_year' => ['required', 'integer', 'min:1450', 'max:' . (date('Y') + 1)],
            'language' =>    ['sometimes', 'string', 'size:2'],
            'pages' =>       ['nullable', 'integer', 'min:1', 'max:10000'],

            'author_id' =>   ['nullable', 'integer', 'exists:authors,id'],
            'genre_ids' =>   ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ];
    }
}
