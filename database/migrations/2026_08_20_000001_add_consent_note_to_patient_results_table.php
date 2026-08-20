<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_results', function (Blueprint $table) {
            // Optional per-result override for the description shown beside the
            // before/after slider. Falls back to the translated default when null.
            $table->text('consent_note')->nullable()->after('after_label');
        });
    }

    public function down(): void
    {
        Schema::table('patient_results', function (Blueprint $table) {
            $table->dropColumn('consent_note');
        });
    }
};
