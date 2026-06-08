<?php

declare(strict_types=1);

namespace App\Modules\Call\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'candidate' => ['required', 'array'], // RTCIceCandidate (serialized)
        ];
    }
}
