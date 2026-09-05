<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a manual sort order to services so the header mega-menu and the
 * category listing can be curated instead of alphabetical.
 *
 * Existing rows are backfilled alphabetically per category (using the English
 * title of each translation group so every language shares one position),
 * which preserves the current on-site order until an admin reorders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('service_category_id')->index();
        });

        $services = DB::table('services')
            ->select(['id', 'service_category_id', 'translation_group_id', 'language', 'title'])
            ->orderBy('title')
            ->get();

        // Anchor each translation group on its English row (fallback: first row) so
        // translations get the same position as the primary language.
        $anchorTitles = $services
            ->whereNotNull('translation_group_id')
            ->groupBy('translation_group_id')
            ->map(fn ($group) => ($group->firstWhere('language', 'en') ?? $group->first())->title);

        $services
            ->groupBy('service_category_id')
            ->each(function ($group) use ($anchorTitles): void {
                $position = 0;
                $assigned = [];

                $group
                    ->sortBy(fn ($service) => $anchorTitles[$service->translation_group_id] ?? $service->title, SORT_NATURAL | SORT_FLAG_CASE)
                    ->each(function ($service) use (&$position, &$assigned): void {
                        $key = $service->translation_group_id ?? 'id:'.$service->id;

                        if (! array_key_exists($key, $assigned)) {
                            $assigned[$key] = ++$position;
                        }

                        DB::table('services')->where('id', $service->id)->update(['sort_order' => $assigned[$key]]);
                    });
            });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
