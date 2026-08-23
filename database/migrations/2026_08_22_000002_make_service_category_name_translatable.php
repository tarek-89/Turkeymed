<?php

use App\Support\Locale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make the service category name translatable by storing it as JSON keyed by
 * locale (e.g. {"en": "Hair Transplant Surgery"}), matching the
 * HasTranslatedFields convention. The slug stays a plain string — it is
 * language-neutral and drives URLs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Preserve existing plain-string names under the default locale before
        // the column type changes to JSON.
        DB::table('service_categories')
            ->whereNotNull('name')
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('service_categories')
                    ->where('id', $row->id)
                    ->update(['name' => json_encode([Locale::DEFAULT => $row->name])]);
            });

        Schema::table('service_categories', function (Blueprint $table): void {
            $table->json('name')->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->string('name', 200)->change();
        });

        // Collapse the JSON back to the default-locale string.
        DB::table('service_categories')
            ->whereNotNull('name')
            ->orderBy('id')
            ->each(function (object $row): void {
                $decoded = json_decode((string) $row->name, true);

                DB::table('service_categories')
                    ->where('id', $row->id)
                    ->update([
                        'name' => is_array($decoded)
                            ? ($decoded[Locale::DEFAULT] ?? reset($decoded) ?: null)
                            : $row->name,
                    ]);
            });
    }
};
