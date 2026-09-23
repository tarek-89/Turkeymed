<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedFields;
use App\Support\Cost\SectionKey;
use App\Support\Locale;
use Database\Factories\CostPageSectionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostPageSection extends Model
{
    /** @use HasFactory<CostPageSectionFactory> */
    use HasFactory;

    use HasTranslatedFields;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'eyebrow' => 'array',
            'title' => 'array',
            'lead' => 'array',
            'content' => 'array',
        ];
    }

    /** @return BelongsTo<CostPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CostPage::class, 'cost_page_id');
    }

    /** @return HasMany<CostPageItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CostPageItem::class)->orderBy('sort_order');
    }

    public function sectionKey(): ?SectionKey
    {
        return SectionKey::tryFrom($this->key);
    }

    /**
     * A section-specific text field from `content`, in the current locale
     * with the usual fallback to English, then to any filled translation.
     */
    public function text(string $field, ?string $locale = null): ?string
    {
        $content = (array) $this->content;
        $locale ??= app()->getLocale();

        $value = $content[$locale][$field]
            ?? $content[Locale::DEFAULT][$field]
            ?? null;

        if (filled($value)) {
            return (string) $value;
        }

        foreach ($content as $translation) {
            if (is_array($translation) && filled($translation[$field] ?? null)) {
                return (string) $translation[$field];
            }
        }

        return null;
    }

    /**
     * Published items of one bullet-list group, in order.
     *
     * @return Collection<int, CostPageItem>
     */
    public function itemsIn(string $group): Collection
    {
        return $this->items
            ->filter(fn (CostPageItem $item): bool => $item->group === $group && $item->is_published)
            ->values();
    }
}
