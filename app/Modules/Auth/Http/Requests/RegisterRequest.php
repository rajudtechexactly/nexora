<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/', Rule::unique('users', 'username')],
            'email'         => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone'         => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')],
            'date_of_birth' => ['nullable', 'date', 'before:-13 years'],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'password'      => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'username.regex'         => 'Username may only contain letters, numbers, underscores and dots.',
            'date_of_birth.before'   => 'You must be at least 13 years old to register.',
        ];
    }
}
