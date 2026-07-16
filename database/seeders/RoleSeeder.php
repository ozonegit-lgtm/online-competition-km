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
            'role_name' => 'Super admin',
            'description' =>'ผู้ดูแลระบบสูงสุด',
        ]);

        Role::updateOrCreate([
            'role_name'   => 'Competition Admin',
            'description' => 'ผู้จัดการแข่งขัน',
        ]);

        Role::updateOrCreate([
            'role_name'   => 'Judge',
            'description' => 'กรรมการตัดสิน',
        ]);
    }
}
