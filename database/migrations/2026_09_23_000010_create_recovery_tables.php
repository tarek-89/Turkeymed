<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recovery timeline of a pricing page:
 *  - stages: the milestones (month, % visible result, texts)
 *  - phases: the coloured bands under the chart (month ranges)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->decimal('month', 4, 1);
            $table->unsignedTinyInteger('percent');
            $table->json('when_label');
            $table->json('short_label');
            $table->json('title');
            $table->json('body')->nullable();
            $table->json('tip')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('recovery_phases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cost_page_id')->constrained()->cascadeOnDelete();
            $table->decimal('from_month', 4, 1);
            $table->decimal('to_month', 4, 1);
            $table->json('name');
            $table->json('short_name')->nullable();
            $table->string('tone', 20)->default('navy');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_phases');
        Schema::dropIfExists('recovery_stages');
    }
};
