<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Steps of the "how is the procedure performed" section. The step number
 * comes from the order; the duration is free text ("~45 min", "2–3 h").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_procedure_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->json('body')->nullable();
            $table->json('duration')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_procedure_steps');
    }
};
