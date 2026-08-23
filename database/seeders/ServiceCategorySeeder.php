<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => ['en' => 'Hair Transplant Surgery'], 'slug' => 'hair-transplant-surgery', 'sort_order' => 1],
            ['name' => ['en' => 'Dental Clinic'], 'slug' => 'dental-clinic', 'sort_order' => 2],
            ['name' => ['en' => 'Eye Surgery'], 'slug' => 'eye-surgery', 'sort_order' => 3],
            ['name' => ['en' => 'Weight Loss'], 'slug' => 'weight-loss', 'sort_order' => 4],
            ['name' => ['en' => 'Services'], 'slug' => 'services', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
