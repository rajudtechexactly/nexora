<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Requests;

use App\Modules\Post\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content'    => ['nullable', 'string', 'max:5000'],
            'visibility' => ['nullable', Rule::in(Post::VISIBILITIES)],
        ];
    }
}
