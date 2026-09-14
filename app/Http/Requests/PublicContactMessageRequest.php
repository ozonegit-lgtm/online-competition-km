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

    public function settings(): KnowledgePageSetting
    {
        return $this->pageSettings ??= KnowledgePageSetting::current();
    }
}
