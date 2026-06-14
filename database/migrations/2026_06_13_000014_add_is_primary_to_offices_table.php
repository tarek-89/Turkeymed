<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('offices', 'is_primary')) {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            // Marks the headquarters / primary location used for the
            // organization's PostalAddress in structured data.
            $table->boolean('is_primary')->default(false)->after('is_published');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('offices', 'is_primary')) {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
