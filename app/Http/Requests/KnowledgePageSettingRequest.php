<?php

namespace App\Http\Requests;

use App\Rules\GoogleMapsEmbedUrl;
use App\Rules\KnowledgeItemFilePolicy;
use App\Rules\SafePageUrl;
use Illuminate\Foundation\Http\FormRequest;

class KnowledgePageSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true
            && $this->user()?->role?->role_name === 'Super Admin';
    }

    public function rules(): array
    {
        $rules = [
            'site_name' => ['sometimes', 'required', 'string', 'max:255'],
            'hero_title' => ['sometimes', 'required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string'],
            'hero_button_label' => ['nullable', 'string', 'max:150'],
            'hero_button_url' => ['nullable', 'string', 'max:2048', new SafePageUrl],
            'about_title' => ['sometimes', 'required', 'string', 'max:255'],
            'about_content' => ['nullable', 'string'],
            'books_title' => ['sometimes', 'required', 'string', 'max:255'],
            'books_description' => ['nullable', 'string'],
            'contact_title' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_description' => ['nullable', 'string'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:254'],
            'map_embed_url' => ['nullable', 'string', 'max:2048', new GoogleMapsEmbedUrl],
            'contact_submit_label' => ['sometimes', 'required', 'string', 'max:150'],
            'footer_organization_name' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string'],
            'footer_address' => ['nullable', 'string'],
            'footer_phone' => ['nullable', 'string', 'max:30'],
            'footer_email' => ['nullable', 'email', 'max:254'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
        ];

        foreach (['hero', 'about', 'books', 'contact'] as $section) {
            $rules["{$section}_enabled"] = ['sometimes', 'boolean'];
            $rules["{$section}_sort_order"] = ['sometimes', 'integer', 'min:0', 'max:4294967295'];
        }
        foreach (['hero_button', 'contact_form'] as $field) {
            $rules["{$field}_enabled"] = ['sometimes', 'boolean'];
        }
        foreach (['name', 'phone', 'email', 'message'] as $field) {
            $rules["contact_{$field}_enabled"] = ['sometimes', 'boolean'];
            $rules["contact_{$field}_required"] = ['sometimes', 'boolean'];
            $rules["contact_{$field}_label"] = ['sometimes', 'required', 'string', 'max:150'];
        }
        foreach (['footer_enabled', 'footer_show_logo', 'footer_show_description', 'footer_show_address', 'footer_show_phone', 'footer_show_email'] as $field) {
            $rules[$field] = ['sometimes', 'boolean'];
        }
        foreach (['site_logo', 'hero_image', 'about_image', 'footer_logo'] as $field) {
            $rules[$field] = ['nullable', 'file', 'max:10240', KnowledgeItemFilePolicy::cover()];
            $rules["remove_{$field}"] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
