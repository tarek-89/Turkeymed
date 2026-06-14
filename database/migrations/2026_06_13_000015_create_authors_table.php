<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('authors')) {
            return;
        }

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // "Dr. Ayşe Yılmaz"
            $table->string('slug')->unique();
            $table->string('credentials')->nullable();    // "MD, Hair Transplant Surgeon"
            $table->string('title')->nullable();          // role / position
            $table->string('photo', 500)->nullable();     // relative path within R2
            $table->json('bio')->nullable();              // translated, keyed by locale
            $table->json('same_as')->nullable();          // list of profile URLs
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
