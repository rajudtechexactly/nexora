<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Requests;

use App\Modules\Post\Models\Reaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(Reaction::TYPES)],
        ];
    }
}
