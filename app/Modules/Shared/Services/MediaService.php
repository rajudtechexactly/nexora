<?php

declare(strict_types=1);

namespace App\Modules\Shared\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Centralised storage + image processing for all user-uploaded media. Keeps
 * upload concerns (paths, resizing, thumbnails, cleanup) out of the domain
 * services so post/reel/chat/profile code stays focused on business rules.
 */
class MediaService
{
    public function disk(): string
    {
        return (string) config('media.disk', 'public');
    }

    /**
     * Store an image, downscaling to a max width and (optionally) producing a
     * thumbnail. Returns relative paths + dimensions.
     *
     * @return array{path: string, thumb_path: ?string, width: int, height: int, size: int, mime: string}
     */
    public function storeImage(UploadedFile $file, string $directory, ?int $maxWidth = null, bool $thumbnail = false): array
    {
        $maxWidth ??= (int) config('media.image.max_width', 1920);
        $filename = $this->uniqueName($file, 'jpg');
        $path     = trim($directory, '/').'/'.$filename;

        $image = Image::decodePath($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $encoded = $image->encodeUsingFileExtension('jpg', quality: 85);
        Storage::disk($this->disk())->put($path, (string) $encoded);

        $thumbPath = null;
        if ($thumbnail) {
            $thumbWidth = (int) config('media.image.thumb_width', 480);
            $thumb = Image::decodePath($file->getRealPath())->scaleDown(width: $thumbWidth);
            $thumbPath = trim($directory, '/').'/thumb_'.$filename;
            Storage::disk($this->disk())->put($thumbPath, (string) $thumb->encodeUsingFileExtension('jpg', quality: 80));
        }

        return [
            'path'       => $path,
            'thumb_path' => $thumbPath,
            'width'      => $image->width(),
            'height'     => $image->height(),
            'size'       => Storage::disk($this->disk())->size($path),
            'mime'       => 'image/jpeg',
        ];
    }

    /**
     * Store a raw file (video/audio/document) untouched.
     *
     * @return array{path: string, size: int, mime: string, original_name: string}
     */
    public function storeFile(UploadedFile $file, string $directory): array
    {
        $filename = $this->uniqueName($file, $file->getClientOriginalExtension() ?: 'bin');
        $path = $file->storeAs(trim($directory, '/'), $filename, $this->disk());

        return [
            'path'          => $path,
            'size'          => $file->getSize() ?: 0,
            'mime'          => $file->getMimeType() ?: 'application/octet-stream',
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    public function url(?string $path): ?string
    {
        return $path ? Storage::disk($this->disk())->url($path) : null;
    }

    public function delete(?string ...$paths): void
    {
        $clean = array_values(array_filter($paths));

        if ($clean !== []) {
            Storage::disk($this->disk())->delete($clean);
        }
    }

    private function uniqueName(UploadedFile $file, string $fallbackExt): string
    {
        return Str::uuid()->toString().'.'.($fallbackExt ?: 'bin');
    }
}
