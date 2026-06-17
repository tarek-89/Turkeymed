<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promote the users table into the authorship entity: a user can now carry
     * a public, credentialed author profile (formerly the dropped `authors`
     * table). `is_published` defaults to false so existing admin accounts are
     * not exposed as public author profiles until explicitly published.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'credentials')) {
                $table->string('credentials')->nullable()->after('slug');      // "MD, Hair Transplant Surgeon"
            }
            if (! Schema::hasColumn('users', 'title')) {
                $table->string('title')->nullable()->after('credentials');      // role / position
            }
            if (! Schema::hasColumn('users', 'photo')) {
                $table->string('photo', 500)->nullable()->after('title');       // relative path within R2
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->json('bio')->nullable()->after('photo');                // translated, keyed by locale
            }
            if (! Schema::hasColumn('users', 'same_as')) {
                $table->json('same_as')->nullable()->after('bio');              // list of profile URLs
            }
            if (! Schema::hasColumn('users', 'is_published')) {
                $table->boolean('is_published')->default(false)->index()->after('same_as');
            }
            if (! Schema::hasColumn('users', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_published');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'credentials', 'title', 'photo', 'bio', 'same_as', 'is_published', 'sort_order',
            ]);
        });
    }
};
