<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'summary')) {
                    // Answer-first lead paragraph — surfaced at the top of the page
                    // and used as the meta-description fallback. Helps AI answers.
                    $table->text('summary')->nullable()->after('excerpt');
                }
                if (! Schema::hasColumn($tableName, 'faqs')) {
                    // [{ "question": ..., "answer": ... }, ...] in the row's language.
                    $table->json('faqs')->nullable();
                }
            });
        }

        Schema::table('testimonials', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonials', 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['summary', 'faqs'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'rating')) {
                $table->dropColumn('rating');
            }
        });
    }
};
