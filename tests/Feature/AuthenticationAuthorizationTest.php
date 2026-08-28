<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser('super-admin', 'Super Admin');

        $this->post(route('login.post'), $this->credentials($user))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_competition_admin_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser('competition-admin', 'Competition Admin');

        $this->post(route('login.post'), $this->credentials($user))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_judge_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser('judge', 'Judge');

        $this->post(route('login.post'), $this->credentials($user))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_incorrect_password_is_rejected(): void
    {
        $user = $this->createUser('judge', 'Judge');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $user = $this->createUser('judge', 'Judge');

        $response = $this->actingAs($user)
            ->withSession(['session-marker' => 'must-be-removed'])
            ->post(route('logout'));

        $response->assertRedirect(route('login'))
            ->assertSessionMissing('session-marker');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_route(): void
    {
        $this->get(route('superadmin.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_judge_cannot_access_super_admin_route(): void
    {
        $judge = $this->createUser('judge', 'Judge');

        $this->actingAs($judge)
            ->get(route('superadmin.dashboard'))
            ->assertForbidden();
    }

    public function test_competition_admin_cannot_access_super_admin_route(): void
    {
        $admin = $this->createUser('competition-admin', 'Competition Admin');

        $this->actingAs($admin)
            ->get(route('superadmin.dashboard'))
            ->assertForbidden();
    }

    public function test_super_admin_cannot_access_competition_admin_route(): void
    {
        $admin = $this->createUser('super-admin', 'Super Admin');

        $this->actingAs($admin)
            ->get(route('competition-admin.dashboard'))
            ->assertForbidden();
    }

    public function test_role_mismatch_returns_forbidden_without_http_500(): void
    {
        $judge = $this->createUser('judge', 'Judge');

        $response = $this->actingAs($judge)
            ->get(route('competition-admin.dashboard'));

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
    }

    /**
     * @return array{email: string, password: string}
     */
    private function credentials(User $user): array
    {
        return [
            'email' => $user->email,
            'password' => 'password123',
        ];
    }

    private function createUser(string $username, string $roleName): User
    {
        $role = Role::create([
            'role_name' => $roleName,
            'display_name' => $roleName,
        ]);

        return User::create([
            'role_id' => $role->id,
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);
    }
}
