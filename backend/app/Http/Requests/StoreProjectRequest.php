<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth handled by Sanctum middleware on the route
    }

    public function rules(): array
    {
        // On update (PUT/PATCH), ignore the current record's slug for uniqueness check
        $projectId = $this->route('id');

        return [
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('projects', 'slug')->ignore($projectId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'role'              => ['nullable', 'string', 'max:150'],
            'stack'             => ['nullable', 'array'],
            'stack.*'           => ['string', 'max:60'],
            'year'              => ['required', 'integer', 'min:2000', 'max:2100'],
            'preview_image_url' => ['nullable', 'string', 'max:500'],
            'live_url'          => ['nullable', 'url', 'max:500'],
            'github_url'        => ['nullable', 'url', 'max:500'],
            'body'              => ['nullable', 'string'],
            'featured'          => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
        ];
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