<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Replace the standalone `authors` table with the users table:
     *  - every author becomes a user (real profile data preserved; the two
     *    fields users require but authors lack — email + password — are filled
     *    with throwaway values),
     *  - posts/services gain a `created_by` FK to users, backfilled from the
     *    old `author_id`,
     *  - the legacy authorship columns and the `authors` table are dropped.
     *
     * The medical reviewer (`reviewer_id` / `last_reviewed_at`) is removed
     * entirely as part of this change.
     *
     * Query builder is used throughout (not Eloquent) because the Author model
     * is deleted alongside this migration.
     */
    public function up(): void
    {
        foreach (['posts', 'services'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('created_by')->nullable()->after('author')->constrained('users')->nullOnDelete();
                });
            }
        }

        /** @var array<int, int> $authorToUser old author id => new user id */
        $authorToUser = [];

        if (Schema::hasTable('authors')) {
            foreach (DB::table('authors')->orderBy('id')->get() as $author) {
                $base = $author->slug ?: Str::slug($author->name) ?: 'author-'.$author->id;

                $userId = DB::table('users')->insertGetId([
                    'name' => $author->name,
                    'slug' => $this->uniqueSlug($base),
                    'email' => $this->uniqueEmail($base),
                    'email_verified_at' => null,
                    'password' => Hash::make(Str::random(40)),
                    'credentials' => $author->credentials,
                    'title' => $author->title,
                    'photo' => $author->photo,
                    'bio' => $author->bio,
                    'same_as' => $author->same_as,
                    'is_published' => $author->is_published,
                    'sort_order' => $author->sort_order,
                    'created_at' => $author->created_at,
                    'updated_at' => $author->updated_at,
                ]);

                $authorToUser[$author->id] = $userId;
            }
        }

        foreach (['posts', 'services'] as $tableName) {
            if (Schema::hasColumn($tableName, 'author_id')) {
                foreach ($authorToUser as $authorId => $userId) {
                    DB::table($tableName)->where('author_id', $authorId)->update(['created_by' => $userId]);
                }
            }
        }

        foreach (['posts', 'services'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'author_id')) {
                    $table->dropConstrainedForeignId('author_id');
                }
                if (Schema::hasColumn($tableName, 'reviewer_id')) {
                    $table->dropConstrainedForeignId('reviewer_id');
                }
                if (Schema::hasColumn($tableName, 'last_reviewed_at')) {
                    $table->dropColumn('last_reviewed_at');
                }
            });
        }

        Schema::dropIfExists('authors');
    }

    /**
     * Reverses the schema only. Author records that were merged into users are
     * not separated back out — this migration is not data-reversible.
     */
    public function down(): void
    {
        if (! Schema::hasTable('authors')) {
            Schema::create('authors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('credentials')->nullable();
                $table->string('title')->nullable();
                $table->string('photo', 500)->nullable();
                $table->json('bio')->nullable();
                $table->json('same_as')->nullable();
                $table->boolean('is_published')->default(true)->index();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

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
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropConstrainedForeignId('created_by');
                }
            });
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 2;

        while (DB::table('users')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function uniqueEmail(string $base): string
    {
        $local = Str::slug($base) ?: 'author';
        $email = $local.'@noreply.invalid';
        $suffix = 2;

        while (DB::table('users')->where('email', $email)->exists()) {
            $email = $local.'-'.$suffix++.'@noreply.invalid';
        }

        return $email;
    }
};
