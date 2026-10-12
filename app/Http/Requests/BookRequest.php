<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book
            ? $this->user()->can('update', $book)
            : $this->user()->can('create', Book::class);
    }

    public function rules(): array
    {
        return [
            'author_id' => ['required', 'exists:authors,id'],
            'isbn' => [
                'required', 'string', 'size:13',
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'title' => ['required', 'string', 'max:255'],
            'published_year' => ['required', 'integer', 'between:1000,'.(date('Y') + 1)],
            'is_reference' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_reference' => $this->boolean('is_reference'),
        ]);
    }
}
