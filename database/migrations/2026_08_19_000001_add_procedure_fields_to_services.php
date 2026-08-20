<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds MedicalProcedure detail fields to services. These feed the enriched
 * MedicalProcedure structured data (bodyLocation, howPerformed, preparation,
 * followup, expectedPrognosis, procedureType) that answer engines cite for
 * "what is / how is / recovery from" medical queries.
 *
 * Each language is a separate service row (grouped by translation_group_id),
 * so these are plain nullable text columns carrying the row's own language —
 * consistent with title/body/summary.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $columns = [
        'procedure_body_location',
        'procedure_preparation',
        'procedure_how_performed',
        'procedure_followup',
        'procedure_expected_prognosis',
        'procedure_type',
    ];

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (! Schema::hasColumn('services', $column)) {
                    // procedure_type is a short label; the rest are free text.
                    if ($column === 'procedure_type') {
                        $table->string($column, 150)->nullable();
                    } else {
                        $table->text($column)->nullable();
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
