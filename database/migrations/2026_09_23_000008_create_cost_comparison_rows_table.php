<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rows of the "Turkey vs abroad" table: a label and one cell per column.
 * Column headers are section texts, so the "other" side can be any country.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_comparison_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->json('ours');
            $table->json('theirs');
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_comparison_rows');
    }
};
