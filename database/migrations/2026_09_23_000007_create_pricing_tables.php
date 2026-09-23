<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Package pricing of a pricing page:
 *  - techniques (FUE / DHI / VIP) and tiers (Basic / Standard / Premium)
 *  - one price per tier × technique
 *  - features, each with a value per tier (included / not / custom text)
 * All five tables belong together, so they ship in one migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_techniques', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('tagline')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricing_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('subtitle')->nullable();
            $table->json('highlights')->nullable();
            $table->json('cta_label')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricing_tier_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_tier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_technique_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price')->nullable();
            $table->timestamps();

            $table->unique(['pricing_tier_id', 'pricing_technique_id'], 'pricing_tier_prices_tier_technique_unique');
        });

        Schema::create('pricing_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricing_feature_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pricing_feature_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_tier_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_included')->default(false);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['pricing_feature_id', 'pricing_tier_id'], 'pricing_feature_values_feature_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_feature_values');
        Schema::dropIfExists('pricing_features');
        Schema::dropIfExists('pricing_tier_prices');
        Schema::dropIfExists('pricing_tiers');
        Schema::dropIfExists('pricing_techniques');
    }
};
