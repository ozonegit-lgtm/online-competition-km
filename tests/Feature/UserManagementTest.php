<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Role $superAdminRole;

    private Role $competitionAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = $this->createRole('Super Admin');
        $this->competitionAdminRole = $this->createRole('Competition Admin');
    }

    public function test_super_admin_can_create_user_with_valid_data(): void
    {
        $admin = $this->createUser('super-admin', $this->superAdminRole);

        $response = $this->actingAs($admin)->post(
            route('superadmin.storeUser'),
            $this->validPayload()
        );

        $response->assertRedirect(route('superadmin.createUser'))
            ->assertSessionHas('success');

        $user = User::where('username', 'new-user')->sole();
        $this->assertSame('new-user@example.com', $user->email);
        $this->assertSame($this->competitionAdminRole->id, $user->role_id);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_username_is_required(): void
    {
        $response = $this->actingAs($this->createUser('admin', $this->superAdminRole))
            ->post(route('superadmin.storeUser'), $this->validPayload(['username' => '']));

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        $admin = $this->createUser('admin', $this->superAdminRole);
        $this->createUser('new-user', $this->competitionAdminRole);

        $response = $this->actingAs($admin)->post(
            route('superadmin.storeUser'),
            $this->validPayload(['email' => 'different@example.com'])
        );

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseCount('users', 2);
    }

    public function test_duplicate_username_does_not_return_http_500(): void
    {
        $admin = $this->createUser('admin', $this->superAdminRole);
        $this->createUser('new-user', $this->competitionAdminRole);

        $response = $this->actingAs($admin)->post(
            route('superadmin.storeUser'),
            $this->validPayload(['email' => 'different@example.com'])
        );

        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('users', 2);
    }

    public function test_email_is_required(): void
    {
        $response = $this->actingAs($this->createUser('admin', $this->superAdminRole))
            ->post(route('superadmin.storeUser'), $this->validPayload(['email' => '']));

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->createUser('admin', $this->superAdminRole);
        $this->createUser('existing', $this->competitionAdminRole, 'new-user@example.com');

        $response = $this->actingAs($admin)->post(
            route('superadmin.storeUser'),
            $this->validPayload(['username' => 'different-user'])
        );

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 2);
    }

    public function test_invalid_role_id_is_rejected(): void
    {
        $response = $this->actingAs($this->createUser('admin', $this->superAdminRole))
            ->post(route('superadmin.storeUser'), $this->validPayload(['role_id' => 999999]));

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_non_super_admin_cannot_create_user(): void
    {
        $user = $this->createUser('competition-admin', $this->competitionAdminRole);

        $this->actingAs($user)
            ->post(route('superadmin.storeUser'), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'new-user']);
    }

    public function test_guest_cannot_create_user(): void
    {
        $this->post(route('superadmin.storeUser'), $this->validPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('users', 0);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $this->competitionAdminRole->id,
            'is_active' => 1,
        ], $overrides);
    }

    private function createRole(string $name): Role
    {
        return Role::create([
            'role_name' => $name,
            'display_name' => $name,
        ]);
    }

    private function createUser(
        string $username,
        Role $role,
        ?string $email = null
    ): User {
        return User::create([
            'role_id' => $role->id,
            'username' => $username,
            'email' => $email ?? $username.'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
