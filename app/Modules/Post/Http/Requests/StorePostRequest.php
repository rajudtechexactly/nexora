<?php

declare(strict_types=1);

namespace App\Modules\Post\Http\Requests;

use App\Modules\Post\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
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
            'media'      => ['nullable', 'array', 'max:10'],
            'media.*'    => [
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime',
                'max:'.(int) config('media.video.max_kb', 204800),
            ],
        ];
    }
}
