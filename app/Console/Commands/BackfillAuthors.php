<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillAuthors extends Command
{
    protected $signature = 'authors:backfill {--apply : Create authors and link content (otherwise dry-run)}';

    protected $description = 'Create Author records from legacy author-name strings on posts and services, then link them.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $names = Post::query()->whereNotNull('author')->where('author', '!=', '')->distinct()->pluck('author')
            ->merge(Service::query()->whereNotNull('author')->where('author', '!=', '')->distinct()->pluck('author'))
            ->map(static fn ($name): string => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        $this->info($names->count().' distinct legacy author name(s) found.');

        $created = 0;
        $linked = 0;

        foreach ($names as $name) {
            $author = Author::query()->where('name', $name)->first();

            if ($author === null) {
                $created++;

                if ($apply) {
                    $author = Author::create([
                        'name' => $name,
                        'slug' => $this->uniqueSlug($name),
                        'is_published' => true,
                        'bio' => [],
                    ]);
                }
            }

            if ($apply && $author !== null) {
                $linked += Post::query()->where('author', $name)->whereNull('author_id')->update(['author_id' => $author->id]);
                $linked += Service::query()->where('author', $name)->whereNull('author_id')->update(['author_id' => $author->id]);
            }
        }

        $this->info(($apply ? 'Created ' : 'Would create ').$created.' author(s).');

        if ($apply) {
            $this->info('Linked '.$linked.' post/service row(s).');
            $this->comment('Authors were created without credentials — add real qualifications in the admin before relying on them for E-E-A-T.');
        } else {
            $this->comment('Dry run. Re-run with --apply to create authors and link content.');
        }

        return self::SUCCESS;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'author';
        $slug = $base;
        $suffix = 2;

        while (Author::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
