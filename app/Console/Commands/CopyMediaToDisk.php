<?php

namespace App\Console\Commands;

use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off move of existing uploads (e.g. from the server disk to R2).
 * Paths stay the same, so the database needs no changes.
 */
class CopyMediaToDisk extends Command
{
    protected $signature = 'media:copy {--from=public : Disk that holds the existing files}';

    protected $description = 'Copy product images and avatars to the media disk (SHOP_MEDIA_DISK), skipping files already there';

    public function handle(): int
    {
        $from = (string) $this->option('from');
        $to = Media::diskName();

        if ($from === $to) {
            $this->error("The media disk is already \"{$to}\". Set SHOP_MEDIA_DISK=r2 first.");

            return self::FAILURE;
        }

        $source = Storage::disk($from);
        $target = Media::disk();
        $copied = $skipped = 0;

        foreach (['products', 'avatars'] as $directory) {
            foreach ($source->allFiles($directory) as $path) {
                if ($target->exists($path)) {
                    $skipped++;

                    continue;
                }

                $target->writeStream($path, $source->readStream($path), [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                    'mimetype' => $source->mimeType($path),
                ]);
                $copied++;
                $this->line("  {$path}");
            }
        }

        $this->info("Copied {$copied} file(s) from \"{$from}\" to \"{$to}\" ({$skipped} already there).");

        return self::SUCCESS;
    }
}
