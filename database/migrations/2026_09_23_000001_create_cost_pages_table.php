<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pricing ("cost") pages: one row per treatment, e.g. "Hair transplant cost in
 * Turkey". Translatable text is stored as JSON keyed by locale, like the other
 * component tables. Everything shown on the page hangs off this row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('slug', 200)->unique();
            $table->boolean('is_published')->default(false)->index();

            // Page text (translatable JSON)
            $table->json('title');
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('og_image', 500)->nullable();

            // Money
            $table->char('currency', 3)->default('EUR');
            $table->unsignedInteger('price_range_min')->nullable();
            $table->unsignedInteger('price_range_max')->nullable();

            // Graft calculator settings (used from Phase 1)
            $table->decimal('hairs_per_graft', 3, 1)->default(2.2);
            $table->unsignedInteger('session_cap_grafts')->default(4500);
            $table->unsignedInteger('meter_max_grafts')->default(5000);
            $table->unsignedInteger('two_session_price_from')->nullable();
            $table->json('duration_rules')->nullable();
            $table->json('default_zone_numbers')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_pages');
    }
};
