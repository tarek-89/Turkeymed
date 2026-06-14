<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            if (Schema::hasColumn($tableName, 'featured_image_meta')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                // { width, height, variants: { 400: path, 800: path, 1200: path } }
                $table->json('featured_image_meta')->nullable()->after('featured_image');
            });
        }
    }

    public function down(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'featured_image_meta')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('featured_image_meta');
            });
        }
    }
};
