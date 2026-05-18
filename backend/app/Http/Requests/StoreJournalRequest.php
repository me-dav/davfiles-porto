<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $entryId = $this->route('id');

        return [
            'title'          => ['required', 'string', 'max:255'],
            'slug'           => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('journal_entries', 'slug')->ignore($entryId),
            ],
            'type'           => [
                'required',
                Rule::in(['exploration', 'server-notes', 'hiking', 'ui-thoughts', 'learning-log']),
            ],
            'hero_image_url' => ['nullable', 'string', 'max:500'],
            'body'           => ['nullable', 'string'],
            'tags'           => ['nullable', 'array'],
            'tags.*'         => ['string', 'max:60'],
            'status'         => ['required', Rule::in(['draft', 'published'])],
        ];
    }

    /**
     * Auto-set published_at when status transitions to published.
     */
    protected function prepareForValidation(): void
    {
        if ($this->status === 'published' && ! $this->published_at) {
            $this->merge(['published_at' => now()]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success'    => false,
                'error'      => 'Validation failed: ' . implode(', ', $validator->errors()->all()),
                'statusCode' => 422,
            ], 422)
        );
    }
}