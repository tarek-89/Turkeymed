<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The scalp zones of a pricing page's graft calculator. `number` (1–6) ties
 * the row to its outline on the head illustration, so zones are edited, not
 * added or removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graft_zones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->json('name');
            $table->unsignedInteger('min_grafts');
            $table->unsignedInteger('max_grafts');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['cost_page_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graft_zones');
    }
};
