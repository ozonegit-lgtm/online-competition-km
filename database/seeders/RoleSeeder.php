<?php

namespace Database\Seeders;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate([
            'role_name' => 'Super Admin',
            'description' =>'ผู้ดูแลระบบสูงสุด',
            'display_name' =>'Super-Admin',
        ]);

        Role::updateOrCreate([
            'role_name'   => 'Competition Admin',
            'description' => 'ผู้จัดการแข่งขัน',
            'display_name' =>'Admin-competition',
        ]);

        Role::updateOrCreate([
            'role_name'   => 'Judge',
            'description' => 'กรรมการตัดสิน',
            'display_name' =>'Judge-competition',
        ]);
    }
}
