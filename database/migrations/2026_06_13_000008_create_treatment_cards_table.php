<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_cards', function (Blueprint $table) {
            $table->id();
            $table->string('variant', 20)->default('default'); // feature | default | cta
            $table->string('icon', 50)->nullable();            // x-ui.icon name
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('badge')->nullable();                 // "Most popular"
            $table->json('footnote')->nullable();              // "From €1,500"
            $table->string('url', 500)->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_cards');
    }
};
