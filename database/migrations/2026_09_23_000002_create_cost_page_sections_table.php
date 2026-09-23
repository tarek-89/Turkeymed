<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per section of a pricing page (calculator, packages, timeline…).
 * The admin can show/hide and reorder sections and edit their headings.
 * Section-specific text lives in `content`, a locale-keyed JSON object
 * whose fields are declared by App\Support\Cost\SectionKey.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('eyebrow')->nullable();
            $table->json('title')->nullable();
            $table->json('lead')->nullable();
            $table->json('content')->nullable();
            $table->timestamps();

            $table->unique(['cost_page_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_page_sections');
    }
};
