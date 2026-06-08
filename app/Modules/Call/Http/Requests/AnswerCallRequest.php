<?php

declare(strict_types=1);

namespace App\Modules\Call\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sdp'      => ['required', 'array'], // RTCSessionDescription answer
            'sdp.type' => ['required', 'string'],
            'sdp.sdp'  => ['required', 'string'],
        ];
    }
}
