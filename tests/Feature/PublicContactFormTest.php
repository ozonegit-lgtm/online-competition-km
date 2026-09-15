<?php

namespace Tests\Feature;

use App\Models\KnowledgePageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_contact_submission_is_stored_and_redirects_to_contact_with_success(): void
    {
        $this->contactSettings();

        $response = $this->from(route('knowledge.index'))->post(route('knowledge.contact.store'), [
            'name' => 'ผู้ติดต่อทดสอบ',
            'phone' => '044-000-000',
            'email' => 'reader@example.org',
            'message' => 'ต้องการสอบถามข้อมูลเพิ่มเติม',
            'website' => '',
        ]);

        $response
            ->assertRedirect($this->contactUrl())
            ->assertSessionHas('success', 'ส่งข้อความเรียบร้อยแล้ว');

        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'ผู้ติดต่อทดสอบ',
            'phone' => '044-000-000',
            'email' => 'reader@example.org',
            'message' => 'ต้องการสอบถามข้อมูลเพิ่มเติม',
            'status' => 'unread',
        ]);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSee('ส่งข้อความเรียบร้อยแล้ว');
    }

    public function test_required_message_failure_redirects_to_contact_and_preserves_input(): void
    {
        $this->contactSettings(['contact_message_required' => true]);

        $response = $this->from(route('knowledge.index'))->post(route('knowledge.contact.store'), [
            'name' => 'ชื่อที่ต้องคงอยู่',
            'phone' => '044-111-111',
            'email' => 'preserved@example.org',
            'message' => '',
            'website' => '',
        ]);

        $response
            ->assertRedirect($this->contactUrl())
            ->assertSessionHasErrors(['message'])
            ->assertSessionHasInput('name', 'ชื่อที่ต้องคงอยู่')
            ->assertSessionHasInput('phone', '044-111-111')
            ->assertSessionHasInput('email', 'preserved@example.org');

        $this->assertDatabaseCount('contact_messages', 0);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSee('ไม่สามารถส่งข้อความได้ กรุณาตรวจสอบข้อมูล')
            ->assertSee('กรุณากรอกข้อความ')
            ->assertSee('value="ชื่อที่ต้องคงอยู่"', false)
            ->assertSee('value="044-111-111"', false)
            ->assertSee('value="preserved@example.org"', false);
    }

    public function test_invalid_email_is_rejected_with_a_readable_error(): void
    {
        $this->contactSettings();

        $this->from(route('knowledge.index'))->post(route('knowledge.contact.store'), [
            'email' => 'invalid-email',
            'message' => 'ข้อความยังต้องคงอยู่',
            'website' => '',
        ])
            ->assertRedirect($this->contactUrl())
            ->assertSessionHasErrors(['email'])
            ->assertSessionHasInput('message', 'ข้อความยังต้องคงอยู่');

        $this->assertDatabaseCount('contact_messages', 0);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSee('กรุณากรอกอีเมลให้ถูกต้อง')
            ->assertSee('ข้อความยังต้องคงอยู่');
    }

    public function test_optional_fields_may_be_empty(): void
    {
        $this->contactSettings([
            'contact_name_required' => false,
            'contact_phone_required' => false,
            'contact_email_required' => false,
        ]);

        $this->post(route('knowledge.contact.store'), [
            'message' => 'ส่งโดยไม่กรอกข้อมูลที่เป็น optional',
            'website' => '',
        ])->assertRedirect($this->contactUrl());

        $this->assertDatabaseHas('contact_messages', [
            'name' => null,
            'phone' => null,
            'email' => null,
            'message' => 'ส่งโดยไม่กรอกข้อมูลที่เป็น optional',
            'status' => 'unread',
        ]);
    }

    public function test_disabled_field_is_not_required_even_when_its_required_setting_is_true(): void
    {
        $this->contactSettings([
            'contact_name_enabled' => false,
            'contact_name_required' => true,
            'contact_phone_enabled' => false,
            'contact_email_enabled' => false,
        ]);

        $this->post(route('knowledge.contact.store'), [
            'message' => 'ส่งโดยไม่มี field ที่ถูกปิด',
            'website' => '',
        ])->assertRedirect($this->contactUrl());

        $this->assertDatabaseHas('contact_messages', [
            'name' => null,
            'message' => 'ส่งโดยไม่มี field ที่ถูกปิด',
            'status' => 'unread',
        ]);
    }

    public function test_honeypot_value_is_rejected_without_an_insert(): void
    {
        $this->contactSettings();

        $this->from(route('knowledge.index'))->post(route('knowledge.contact.store'), [
            'message' => 'ข้อความจาก bot',
            'website' => 'https://spam.example',
        ])
            ->assertRedirect($this->contactUrl())
            ->assertSessionHasErrors(['website']);

        $this->assertDatabaseCount('contact_messages', 0);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSee('ไม่สามารถส่งข้อความได้')
            ->assertSeeInOrder([
                'name="website"',
                'value=""',
                'tabindex="-1"',
                'autocomplete="off"',
                'aria-hidden="true"',
            ], false);
    }

    private function contactSettings(array $overrides = []): KnowledgePageSetting
    {
        return KnowledgePageSetting::create(array_merge([
            'site_name' => 'Contact Test',
            'contact_enabled' => true,
            'contact_form_enabled' => true,
            'contact_name_enabled' => true,
            'contact_name_required' => false,
            'contact_phone_enabled' => true,
            'contact_phone_required' => false,
            'contact_email_enabled' => true,
            'contact_email_required' => false,
            'contact_message_enabled' => true,
            'contact_message_required' => true,
        ], $overrides));
    }

    private function contactUrl(): string
    {
        return route('knowledge.index').'#contact';
    }
}
