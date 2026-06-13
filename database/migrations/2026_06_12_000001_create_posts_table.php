<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Link back to wp_posts.ID — makes the import idempotent (re-runnable)
            $table->unsignedBigInteger('wp_post_id')->nullable()->unique();

            // Polylang translation group (term_taxonomy_id of the 'post_translations' term).
            // Posts alone in their group = single-language posts (translate later = new row, same group).
            $table->unsignedBigInteger('translation_group_id')->nullable()->index();

            $table->string('language', 10)->index();      // en, ar, es, it, fr, ...
            $table->string('slug', 300);                  // decoded UTF-8 (Arabic slugs stored readable)
            $table->string('title', 500);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();         // raw post_content HTML
            $table->string('featured_image', 500)->nullable(); // relative path within uploads/
            $table->string('author')->nullable();

            // Rank Math SEO (null = no manual override; render your default template in Blade)
            $table->string('meta_title', 500)->nullable();
            $table->string('meta_description', 1000)->nullable();
            $table->string('focus_keyword', 255)->nullable();

            // True when content was built with Elementor (_elementor_data present) —
            // these need a render-or-rebuild decision, body may be empty/stale.
            $table->boolean('is_elementor')->default(false)->index();

            $table->string('status', 20)->default('draft')->index(); // publish | draft
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('wp_modified_at')->nullable();         // for delta-sync comparisons

            $table->timestamps();

            $table->index(['language', 'slug']); // route lookups: /{locale}/{slug}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
