<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('role_name', 'Super Admin')->first();

        if (! $role) {
            $this->command->error('Role "Super Admin" not found.');

            return;
        }

        $user = User::where('email', 'admin@competitionkm.com')->first();

        /*
        |--------------------------------------------------------------------------
        | Existing Super Admin
        |--------------------------------------------------------------------------
        |
        | Seeder ต้องไม่เปลี่ยน password, role หรือ is_active ของบัญชีที่มีอยู่แล้ว
        |
        */
        if ($user) {
            if ((int) $user->role_id !== (int) $role->id) {
                throw new RuntimeException(
                    'Existing admin@competitionkm.com is not a Super Admin.'
                );
            }

            AdminProfile::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
                    'phone' => null,
                    'position' => 'Super Administrator',
                    'avatar' => null,
                    'must_change_password' => false,
                ]
            );

            $this->command->info(
                'Super Admin already exists. Existing credentials and account status were left unchanged.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | New Super Admin
        |--------------------------------------------------------------------------
        |
        | ไม่มี default password
        | ต้องกำหนด SUPER_ADMIN_PASSWORD ก่อนรัน Seeder
        |
        */
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException(
                'SUPER_ADMIN_PASSWORD must be set and contain at least 12 characters.'
            );
        }

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'superadmin',
            'email' => 'admin@competitionkm.com',
            'password' => Hash::make($password),
            'is_active' => true,
            'last_login_at' => null,
        ]);

        AdminProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone' => null,
            'position' => 'Super Administrator',
            'avatar' => null,
            'must_change_password' => true,
        ]);

        $this->command->info('Super Admin created successfully.');
    }
}