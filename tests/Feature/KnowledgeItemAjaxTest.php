<?php

namespace Tests\Feature;

use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KnowledgeItemAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_actions_return_updated_targets_and_detail_delete_redirect_is_explicit(): void
    {
        $role = DB::table('roles')->insertGetId(['role_name' => 'Competition Admin', 'display_name' => 'Competition Admin']);
        $owner = User::create(['role_id' => $role, 'username' => 'owner', 'email' => 'owner@example.com', 'password' => 'password', 'is_active' => true]);
        $item = KnowledgeItem::create(['created_by' => $owner->id, 'title' => 'AJAX item', 'status' => 'draft']);
        $index = route('competition-admin.km.index');
        $show = route('competition-admin.km.show', $item);
        $this->actingAs($owner)->get($index)->assertOk()
            ->assertSee('id="km-list"', false)->assertSee('id="km-total"', false)
            ->assertSee('data-ajax-target="#km-list, #km-total"', false)
            ->assertSee(route('competition-admin.km.publish', $item));
        $this->from($index)->post(route('competition-admin.km.publish', $item), [], ['X-Requested-With' => 'XMLHttpRequest'])->assertRedirect($index);
        $this->get($index)->assertSee('published')->assertSee(route('competition-admin.km.unpublish', $item));
        $this->get($show)->assertOk()->assertSee('id="km-detail"', false)
            ->assertSee('data-ajax-target="#km-detail"', false)
            ->assertSee('data-ajax-redirect="'.$index.'"', false);
        $this->from($show)->delete(route('competition-admin.km.unpublish', $item), [], ['X-Requested-With' => 'XMLHttpRequest'])->assertRedirect($show);
        $this->assertDatabaseHas('knowledge_items', [
            'id' => $item->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
        $this->get($show)->assertSee('ฉบับร่าง')->assertSee(route('competition-admin.km.publish', $item));
        $filteredIndex = $index.'?search=AJAX&status=draft';
        $this->from($filteredIndex)->delete(route('competition-admin.km.destroy', $item), [], ['X-Requested-With' => 'XMLHttpRequest'])->assertRedirect($filteredIndex);
        $this->get($index)->assertDontSee('AJAX item')->assertSee('id="km-list"', false);
    }
}
