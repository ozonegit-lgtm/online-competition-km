<?php

namespace Database\Seeders;

use App\Models\CompetitionCategory;
use Illuminate\Database\Seeder;

class CompetitionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [

            [
                'category_name' => 'Poster',
                'category_slug' => 'poster',
                'description' => 'การแข่งขันออกแบบโปสเตอร์',
                'is_active' => true,
            ],

            [
                'category_name' => 'Innovation',
                'category_slug' => 'innovation',
                'description' => 'การแข่งขันนวัตกรรม',
                'is_active' => true,
            ],

            [
                'category_name' => 'Hackathon',
                'category_slug' => 'hackathon',
                'description' => 'การแข่งขัน Hackathon',
                'is_active' => true,
            ],

            [
                'category_name' => 'Research',
                'category_slug' => 'research',
                'description' => 'การแข่งขันผลงานวิจัย',
                'is_active' => true,
            ],

            [
                'category_name' => 'Startup',
                'category_slug' => 'startup',
                'description' => 'การแข่งขัน Startup',
                'is_active' => true,
            ],

        ];

        foreach ($categories as $category) {

            CompetitionCategory::updateOrCreate(
                [
                    'category_slug' => $category['category_slug']
                ],
                $category
            );

        }
    }
}