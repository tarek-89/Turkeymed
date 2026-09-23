<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Quick pick" buttons of the graft calculator (e.g. Norwood stages): a label
 * and the zone numbers the button selects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graft_presets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->json('zone_numbers');
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graft_presets');
    }
};
