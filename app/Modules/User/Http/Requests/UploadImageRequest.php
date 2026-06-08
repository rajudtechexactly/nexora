<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb  = (int) config('media.image.max_kb', 8192);
        $mimes  = implode(',', config('media.image.mimes', ['jpg', 'jpeg', 'png', 'webp']));

        return [
            'image' => ['required', 'image', "mimes:{$mimes}", "max:{$maxKb}"],
        ];
    }
}
