<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unlisted pricing pages: published, but kept out of navigation, sitemap,
 * llms.txt and search engines, and optionally reachable only with a key in
 * the link (?key=…) so they can be shared with specific people.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_pages', function (Blueprint $table): void {
            $table->boolean('is_unlisted')->default(false)->after('is_published');
            $table->string('access_key', 64)->nullable()->after('is_unlisted');
        });
    }

    public function down(): void
    {
        Schema::table('cost_pages', function (Blueprint $table): void {
            $table->dropColumn(['is_unlisted', 'access_key']);
        });
    }
};
