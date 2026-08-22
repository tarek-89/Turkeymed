<?php

use App\Support\Locale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make the patient result overlay labels and description translatable by
 * storing them as JSON keyed by locale (e.g. {"en": "After — month 12"}),
 * matching the HasTranslatedFields convention used elsewhere.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $fields = ['before_label', 'after_label', 'consent_note'];

    public function up(): void
    {
        // Preserve any existing plain-string values by wrapping them under the
        // default locale before the column type changes to JSON.
        foreach ($this->fields as $field) {
            DB::table('patient_results')
                ->whereNotNull($field)
                ->orderBy('id')
                ->each(function (object $row) use ($field): void {
                    DB::table('patient_results')
                        ->where('id', $row->id)
                        ->update([
                            $field => json_encode([Locale::DEFAULT => $row->{$field}]),
                        ]);
                });
        }

        Schema::table('patient_results', function (Blueprint $table): void {
            $table->json('before_label')->nullable()->change();
            $table->json('after_label')->nullable()->change();
            $table->json('consent_note')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patient_results', function (Blueprint $table): void {
            $table->string('before_label', 100)->nullable()->change();
            $table->string('after_label', 100)->nullable()->change();
            $table->text('consent_note')->nullable()->change();
        });

        // Collapse the JSON back to the default-locale string.
        foreach ($this->fields as $field) {
            DB::table('patient_results')
                ->whereNotNull($field)
                ->orderBy('id')
                ->each(function (object $row) use ($field): void {
                    $decoded = json_decode((string) $row->{$field}, true);

                    DB::table('patient_results')
                        ->where('id', $row->id)
                        ->update([
                            $field => is_array($decoded)
                                ? ($decoded[Locale::DEFAULT] ?? reset($decoded) ?: null)
                                : $row->{$field},
                        ]);
                });
        }
    }
};
