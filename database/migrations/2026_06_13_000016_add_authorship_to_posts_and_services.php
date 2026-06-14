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
                if (! Schema::hasColumn($tableName, 'author_id')) {
                    $table->foreignId('author_id')->nullable()->after('author')->constrained('authors')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'reviewer_id')) {
                    $table->foreignId('reviewer_id')->nullable()->after('author_id')->constrained('authors')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'last_reviewed_at')) {
                    $table->timestamp('last_reviewed_at')->nullable()->after('reviewer_id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('author_id');
                $table->dropConstrainedForeignId('reviewer_id');
                $table->dropColumn('last_reviewed_at');
            });
        }
    }
};
