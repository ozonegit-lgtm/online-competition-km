<?php

namespace Database\Seeders;

use App\Models\CompetitionTemplate;
use Illuminate\Database\Seeder;

class CompetitionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [

            [
                'template_name' => 'Poster Competition',
                'template_slug' => 'poster-competition',
                'default_description' => 'แม่แบบสำหรับการแข่งขันออกแบบโปสเตอร์',
                'cover_image' => null,
                'is_active' => true,
            ],

            [
                'template_name' => 'Innovation Contest',
                'template_slug' => 'innovation-contest',
                'default_description' => 'แม่แบบสำหรับการแข่งขันนวัตกรรม',
                'cover_image' => null,
                'is_active' => true,
            ],

            [
                'template_name' => 'Hackathon',
                'template_slug' => 'hackathon',
                'default_description' => 'แม่แบบสำหรับการแข่งขัน Hackathon',
                'cover_image' => null,
                'is_active' => true,
            ],

            [
                'template_name' => 'Startup Pitch',
                'template_slug' => 'startup-pitch',
                'default_description' => 'แม่แบบสำหรับการแข่งขัน Startup Pitch',
                'cover_image' => null,
                'is_active' => true,
            ],

        ];

        foreach ($templates as $template) {

            CompetitionTemplate::updateOrCreate(
                [
                    'template_slug' => $template['template_slug']
                ],
                $template
            );

        }
    }
}