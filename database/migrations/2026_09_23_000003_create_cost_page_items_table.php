<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simple ordered bullet lists that belong to a pricing-page section: hero
 * badges, "you save" points, payment methods, promise points, advantages…
 * `group` says which list inside the section the item belongs to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_page_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_section_id')->constrained()->cascadeOnDelete();
            $table->string('group', 40)->index();
            $table->json('title');
            $table->json('body')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_page_items');
    }
};
