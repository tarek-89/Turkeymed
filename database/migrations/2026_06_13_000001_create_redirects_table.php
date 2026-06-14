<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            // Old path to match, normalised: no host, no leading/trailing slash,
            // percent-decoded (e.g. "category/hair-transplant"). Unique so a given
            // source URL only ever points one place — never a redirect chain.
            $table->string('from_path', 500)->unique();

            // Destination: a root-relative path ("/hair-transplant-surgery") or an
            // absolute URL. Resolved by App\Models\Redirect::target().
            $table->string('to_path', 500);

            $table->unsignedSmallInteger('status_code')->default(301); // 301 permanent | 302 temporary
            $table->boolean('is_active')->default(true)->index();

            // Where the row came from (e.g. "wp-import") and free-text notes for
            // entries that still need a human to confirm the destination.
            $table->string('source', 50)->nullable();
            $table->string('notes', 500)->nullable();

            // Lightweight usage tracking so dead redirects can be pruned later.
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
