<?php

declare(strict_types=1);

namespace App\Modules\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body'       => ['nullable', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,application/pdf,video/mp4',
                'max:'.(int) config('media.video.max_kb', 204800),
            ],
        ];
    }
}
