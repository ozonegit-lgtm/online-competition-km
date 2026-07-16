<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ค้นหา Role Super Admin
        $role = Role::where('role_name', 'Super Admin')->first();

        if (!$role) {
            $this->command->error('Role Super Admin not found.');
            return;
        }

        // สร้าง Super Admin
        $user = User::updateOrCreate(
            [
                'email' => 'admin@competitionkm.com',
            ],
            [
                'role_id'       => $role->id,
                'password'      => Hash::make('12345678'),
                'status'        => 'active',
                'last_login_at' => null,
            ]
        );

        // สร้าง Admin Profile
        AdminProfile::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'first_name'         => 'Super',
                'last_name'          => 'Admin',
                'phone'              => '0812345678',
                'organization'       => 'Online Competition KM',
                'profile_image'      => null,
                'approval_status'    => 'approved',
                'approved_by_user_id'=> $user->id,
                'approved_at'        => now(),
            ]
        );
    }
}