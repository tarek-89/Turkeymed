<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Countries in the "prices compared worldwide" chart of a pricing page, each
 * with its typical price range. The Turkey figure and the savings are
 * calculated from the page's own prices, never stored here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_countries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->char('country_code', 2);
            $table->json('name');
            $table->json('name_in_sentence')->nullable();
            $table->unsignedInteger('min_price');
            $table->unsignedInteger('max_price');
            $table->json('note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_countries');
    }
};
