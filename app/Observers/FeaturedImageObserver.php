<?php

namespace App\Observers;

use App\Support\Images\ResponsiveImageGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FeaturedImageObserver
{
    public function __construct(private ResponsiveImageGenerator $generator) {}

    /** A freshly created record with an image gets its variants generated. */
    public function created(Model $model): void
    {
        if (filled($model->getAttribute('featured_image'))) {
            $this->process($model);
        }
    }

    /** On update, (re)generate only when the image actually changed. */
    public function updated(Model $model): void
    {
        if ($model->wasChanged('featured_image')) {
            $this->process($model);
        }
    }

    /**
     * (Re)generate responsive WebP variants and persist the metadata. Runs only
     * when R2 is configured, so local/test environments without storage stay
     * untouched. Writes back quietly to avoid re-triggering model events.
     */
    private function process(Model $model): void
    {
        if (blank(config('filesystems.disks.r2.key'))) {
            return;
        }

        $meta = blank($model->getAttribute('featured_image'))
            ? null
            : $this->generator->generate(Storage::disk('r2'), (string) $model->getAttribute('featured_image'));

        $model->forceFill(['featured_image_meta' => $meta])->saveQuietly();
    }
}
