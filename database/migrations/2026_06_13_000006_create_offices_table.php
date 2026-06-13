<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->json('country');           // {"en": "Türkiye", ...} — also the grouping key
            $table->json('name');              // {"en": "Istanbul · Şişli", ...}
            $table->json('address');           // {"en": "...", ...}
            $table->json('hours')->nullable(); // {"en": "Mon–Sat · 09:00–19:00", ...}
            $table->json('badge')->nullable(); // {"en": "Headquarters", ...}
            $table->string('phone', 40)->nullable();
            $table->string('directions_url', 1000)->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
