<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    private Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::create([
            'role_name' => 'Super Admin',
            'display_name' => 'Super Admin',
        ]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $admin = $this->createUser('admin');
        $category = $this->createCategory('unused');

        $response = $this->actingAs($admin)
            ->delete(route('superadmin.categories.destroy', $category));

        $response->assertRedirect(route('superadmin.categories.create'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('competition_categories', ['id' => $category->id]);
    }

    public function test_referenced_category_returns_a_useful_error_instead_of_500(): void
    {
        $admin = $this->createUser('admin');
        $category = $this->createCategory('used');
        $this->createCompetition($category, $admin);

        $response = $this->actingAs($admin)
            ->delete(route('superadmin.categories.destroy', $category));

        $response->assertRedirect(route('superadmin.categories.create'))
            ->assertSessionHas('error', 'ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีการแข่งขันใช้งานอยู่');
        $this->assertDatabaseHas('competition_categories', ['id' => $category->id]);
    }

    public function test_referenced_user_returns_a_useful_error_instead_of_500(): void
    {
        $admin = $this->createUser('admin');
        $creator = $this->createUser('creator');
        $this->createCompetition($this->createCategory('general'), $creator);

        $response = $this->actingAs($admin)
            ->delete(route('superadmin.deleteUser', $creator));

        $response->assertRedirect(route('superadmin.showUser', $creator))
            ->assertSessionHas('error', 'ไม่สามารถลบบัญชีนี้ได้ เนื่องจากมีข้อมูลการแข่งขันอ้างอิงอยู่');
        $this->assertDatabaseHas('users', ['id' => $creator->id]);
    }

    public function test_super_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)
            ->delete(route('superadmin.deleteUser', $admin));

        $response->assertRedirect(route('superadmin.showUser', $admin))
            ->assertSessionHas('error', 'ไม่สามารถลบบัญชีที่กำลังเข้าสู่ระบบอยู่ได้');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_super_admin_cannot_update_their_own_account(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->put(route('superadmin.updateUser', $admin), [
            'username' => 'changed',
            'email' => 'changed@example.com',
            'role_id' => $admin->role_id,
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('superadmin.showUser', $admin))
            ->assertSessionHas('error', 'ไม่สามารถแก้ไขบัญชีที่กำลังเข้าสู่ระบบอยู่ได้');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'username' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_update_and_delete_another_user(): void
    {
        $admin = $this->createUser('admin');
        $other = $this->createUser('other');

        $this->actingAs($admin)->put(route('superadmin.updateUser', $other), [
            'username' => 'updated-other',
            'email' => 'updated-other@example.com',
            'role_id' => $other->role_id,
            'is_active' => 0,
        ])->assertRedirect(route('superadmin.showUser', $other));

        $this->assertDatabaseHas('users', [
            'id' => $other->id,
            'username' => 'updated-other',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('superadmin.deleteUser', $other))
            ->assertRedirect(route('superadmin.createUser'));
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    private function createUser(string $username): User
    {
        return User::create([
            'role_id' => $this->superAdminRole->id,
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function createCategory(string $slug): CompetitionCategory
    {
        return CompetitionCategory::create([
            'category_name' => ucfirst($slug),
            'category_slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function createCompetition(CompetitionCategory $category, User $creator): Competition
    {
        return Competition::create([
            'category_id' => $category->id,
            'created_by' => $creator->id,
            'title' => 'Test competition',
            'registration_start' => now(),
            'registration_end' => now()->addDay(),
        ]);
    }
}