<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('wp_post_id')->nullable()->unique();

            $table->foreignId('service_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('translation_group_id')->nullable()->index();

            $table->string('language', 10)->index();
            $table->string('slug', 300);
            $table->string('title', 500);
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('featured_image', 500)->nullable();
            $table->string('author')->nullable();

            // Rank Math SEO
            $table->string('meta_title', 500)->nullable();
            $table->string('meta_description', 1000)->nullable();
            $table->string('focus_keyword', 255)->nullable();

            $table->boolean('is_elementor')->default(false)->index();

            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('wp_modified_at')->nullable();

            $table->timestamps();

            $table->index(['language', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
