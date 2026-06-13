<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

/**
 * Default social links (placeholders). Seeds only when the table is empty,
 * so admin edits are never overwritten.
 */
class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        if (SocialLink::query()->exists()) {
            return;
        }

        $links = [
            ['platform' => 'instagram', 'url' => 'https://instagram.com/turkeymed', 'sort_order' => 1],
            ['platform' => 'facebook', 'url' => 'https://facebook.com/turkeymed', 'sort_order' => 2],
            ['platform' => 'youtube', 'url' => 'https://youtube.com/@turkeymed', 'sort_order' => 3],
            ['platform' => 'telegram', 'url' => 'https://t.me/turkeymed', 'sort_order' => 4],
            ['platform' => 'tiktok', 'url' => 'https://tiktok.com/@turkeymed', 'sort_order' => 5],
        ];

        foreach ($links as $link) {
            SocialLink::create($link + ['is_published' => true]);
        }
    }
}
