<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'                => ['sometimes', 'string', 'max:255'],
            'phone'               => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'date_of_birth'       => ['sometimes', 'nullable', 'date', 'before:-13 years'],
            'gender'              => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other'])],
            'bio'                 => ['sometimes', 'nullable', 'string', 'max:1000'],
            'location'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'website'             => ['sometimes', 'nullable', 'url', 'max:255'],
            'work'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'education'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'relationship_status' => ['sometimes', 'nullable', Rule::in(['single', 'in_a_relationship', 'engaged', 'married', 'complicated', 'private'])],
            'visibility'          => ['sometimes', Rule::in(['public', 'friends', 'private'])],
        ];
    }
}
