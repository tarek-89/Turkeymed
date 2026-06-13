<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_category_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional: pin a result to one specific service (shown there first).
            $table->foreignId('service_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('before_image', 500);
            $table->string('after_image', 500);

            // Structured metadata — the public headline is built from these via
            // translation strings, keeping results language-neutral.
            $table->unsignedInteger('grafts_count')->nullable();
            $table->unsignedSmallInteger('months_to_result')->nullable();

            // Optional overrides for the image overlay labels.
            $table->string('before_label', 100)->nullable();
            $table->string('after_label', 100)->nullable();

            $table->boolean('consent_confirmed')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['service_category_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_results');
    }
};
