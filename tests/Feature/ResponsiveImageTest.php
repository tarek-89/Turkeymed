<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Images\ResponsiveImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResponsiveImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_creates_webp_variants(): void
    {
        Storage::fake('r2');
        $path = '2026/06/photo.jpg';
        Storage::disk('r2')->put($path, UploadedFile::fake()->image('photo.jpg', 1600, 1000)->getContent());

        $meta = (new ResponsiveImageGenerator)->generate(Storage::disk('r2'), $path);

        $this->assertNotNull($meta);
        $this->assertSame(1600, $meta['width']);
        $this->assertSame(1000, $meta['height']);
        Storage::disk('r2')->assertExists('2026/06/photo-400.webp');
        Storage::disk('r2')->assertExists('2026/06/photo-800.webp');
        Storage::disk('r2')->assertExists('2026/06/photo-1200.webp');
    }

    public function test_generator_never_upscales_small_images(): void
    {
        Storage::fake('r2');
        Storage::disk('r2')->put('small.jpg', UploadedFile::fake()->image('small.jpg', 300, 200)->getContent());

        $meta = (new ResponsiveImageGenerator)->generate(Storage::disk('r2'), 'small.jpg');

        $this->assertNotNull($meta);
        $this->assertSame([], $meta['variants']);
        Storage::disk('r2')->assertMissing('small-400.webp');
    }

    public function test_saving_a_post_populates_meta_and_srcset(): void
    {
        Storage::fake('r2');
        config([
            'filesystems.disks.r2.key' => 'test',
            'filesystems.disks.r2.url' => 'https://media.test',
        ]);
        $path = '2026/06/hero.jpg';
        Storage::disk('r2')->put($path, UploadedFile::fake()->image('hero.jpg', 1600, 1000)->getContent());

        $post = Post::factory()->create(['featured_image' => $path]);
        $post->refresh();

        $this->assertNotNull($post->featured_image_meta);
        $this->assertStringContainsString('800w', (string) $post->featuredImageSrcset());
        $this->assertStringContainsString('.webp', (string) $post->featuredImageSrcset());
    }

    public function test_unconfigured_storage_is_skipped_cleanly(): void
    {
        // No R2 key configured: the observer must not touch storage or fail.
        config(['filesystems.disks.r2.key' => null]);

        $post = Post::factory()->create(['featured_image' => 'whatever.jpg']);

        $this->assertNull($post->fresh()->featured_image_meta);
    }

    public function test_backfill_command_generates_variants_for_existing_images(): void
    {
        Storage::fake('r2');
        $path = '2026/06/legacy.jpg';
        Storage::disk('r2')->put($path, UploadedFile::fake()->image('legacy.jpg', 1600, 1000)->getContent());

        // Created while storage is unconfigured, so it has no metadata yet.
        config(['filesystems.disks.r2.key' => null]);
        $post = Post::factory()->create(['featured_image' => $path]);
        $this->assertNull($post->fresh()->featured_image_meta);

        config(['filesystems.disks.r2.key' => 'test', 'filesystems.disks.r2.url' => 'https://media.test']);
        $this->artisan('images:variants')->assertSuccessful();

        $this->assertNotNull($post->fresh()->featured_image_meta);
        Storage::disk('r2')->assertExists('2026/06/legacy-800.webp');
    }
}
