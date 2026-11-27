<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Where uploaded images (product photos, avatars) live: the local "public"
 * disk by default, or Cloudflare R2 with SHOP_MEDIA_DISK=r2. Files on a
 * hosting platform's own disk can disappear on every deploy; R2 keeps them.
 */
class Media
{
    public static function diskName(): string
    {
        return (string) config('shop.media_disk', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    /**
     * Store an upload under $directory and return its path on the disk.
     * Names are random and never reused, so browsers and Cloudflare may
     * cache the file for a year.
     */
    public static function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, [
            'disk' => self::diskName(),
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        if ($path === false) {
            throw new \RuntimeException("Could not store the upload in {$directory}.");
        }

        return $path;
    }

    public static function url(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return self::disk()->url(ltrim(trim($path), '/'));
    }

    /**
     * Delete a stored file. A missing file or a storage error is logged, not
     * thrown: a leftover photo must never block deleting a product.
     */
    public static function delete(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        try {
            self::disk()->delete($path);
        } catch (\Throwable $e) {
            Log::warning('Media file could not be deleted.', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }
}
