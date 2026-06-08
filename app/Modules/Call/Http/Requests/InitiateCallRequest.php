<?php

declare(strict_types=1);

namespace App\Modules\Call\Http\Requests;

use App\Modules\Call\Models\Call;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'callee_id' => ['required', 'integer', 'exists:users,id'],
            'type'      => ['required', Rule::in([Call::TYPE_AUDIO, Call::TYPE_VIDEO])],
            'sdp'       => ['required', 'array'], // RTCSessionDescription offer
            'sdp.type'  => ['required', 'string'],
            'sdp.sdp'   => ['required', 'string'],
        ];
    }
}
