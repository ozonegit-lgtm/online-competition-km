<?php

namespace App\Http\Requests;

use App\Models\KnowledgePageSetting;
use Illuminate\Foundation\Http\FormRequest;

class PublicContactMessageRequest extends FormRequest
{
    private ?KnowledgePageSetting $pageSettings = null;

    public function authorize(): bool
    {
        $settings = $this->settings();

        return $settings->contact_enabled
            && $settings->contact_form_enabled
            && collect(['name', 'phone', 'email', 'message'])
                ->contains(fn (string $field) => $settings->{"contact_{$field}_enabled"});
    }

    public function rules(): array
    {
        $settings = $this->settings();
        $limits = ['name' => 150, 'phone' => 30, 'email' => 254, 'message' => 10000];
        $rules = ['website' => ['nullable', 'string', 'max:0']];

        foreach ($limits as $field => $limit) {
            if (! $settings->{"contact_{$field}_enabled"}) {
                $rules[$field] = ['prohibited'];

                continue;
            }

            $rules[$field] = [
                $settings->{"contact_{$field}_required"} ? 'required' : 'nullable',
                $field === 'email' ? 'email' : 'string',
                "max:{$limit}",
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'กรุณากรอกชื่อ',
            'name.string' => 'กรุณากรอกชื่อให้ถูกต้อง',
            'name.max' => 'ชื่อต้องมีความยาวไม่เกิน 150 ตัวอักษร',
            'name.prohibited' => 'ไม่สามารถส่งข้อมูลชื่อได้',
            'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'phone.string' => 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง',
            'phone.max' => 'เบอร์โทรศัพท์ต้องมีความยาวไม่เกิน 30 ตัวอักษร',
            'phone.prohibited' => 'ไม่สามารถส่งข้อมูลเบอร์โทรศัพท์ได้',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'กรุณากรอกอีเมลให้ถูกต้อง',
            'email.max' => 'อีเมลต้องมีความยาวไม่เกิน 254 ตัวอักษร',
            'email.prohibited' => 'ไม่สามารถส่งข้อมูลอีเมลได้',
            'message.required' => 'กรุณากรอกข้อความ',
            'message.string' => 'กรุณากรอกข้อความให้ถูกต้อง',
            'message.max' => 'ข้อความต้องมีความยาวไม่เกิน 10,000 ตัวอักษร',
            'message.prohibited' => 'ไม่สามารถส่งข้อมูลข้อความได้',
            'website.string' => 'ไม่สามารถส่งข้อความได้',
            'website.max' => 'ไม่สามารถส่งข้อความได้',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('knowledge.index').'#contact';
    }

    public function settings(): KnowledgePageSetting
    {
        return $this->pageSettings ??= KnowledgePageSetting::current();
    }
}
