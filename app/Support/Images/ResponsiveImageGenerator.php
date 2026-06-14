<?php

namespace App\Support\Images;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class ResponsiveImageGenerator
{
    /**
     * Target widths (px) for the responsive WebP variants.
     *
     * @var list<int>
     */
    public const WIDTHS = [400, 800, 1200];

    /**
     * Generate WebP variants of an image already stored on the disk and return
     * its metadata. Never throws — returns null when the file is missing or
     * cannot be processed, so it can run safely inside a model save.
     *
     * @return array{width: int, height: int, variants: array<int, string>}|null
     */
    public function generate(Filesystem $disk, string $path): ?array
    {
        try {
            if (! $disk->exists($path)) {
                return null;
            }

            $data = $disk->get($path);
            if ($data === null || $data === '') {
                return null;
            }

            $manager = new ImageManager(new Driver);

            $base = $manager->read($data);
            $width = $base->width();
            $height = $base->height();

            $directory = trim(dirname($path), '.');
            $name = pathinfo($path, PATHINFO_FILENAME);
            $variants = [];

            foreach (self::WIDTHS as $targetWidth) {
                if ($targetWidth >= $width) {
                    continue; // never upscale
                }

                $encoded = $manager->read($data)->scaleDown(width: $targetWidth)->toWebp(75);
                $variantPath = ($directory !== '' ? $directory.'/' : '').$name.'-'.$targetWidth.'.webp';
                $disk->put($variantPath, (string) $encoded);
                $variants[$targetWidth] = $variantPath;
            }

            return ['width' => $width, 'height' => $height, 'variants' => $variants];
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
