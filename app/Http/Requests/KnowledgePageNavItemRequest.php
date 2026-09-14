<?php

namespace App\Http\Requests;

use App\Models\KnowledgePageNavItem;
use App\Rules\SafePageUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KnowledgePageNavItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true
            && $this->user()?->role?->role_name === 'Super Admin';
    }

    public function rules(): array
    {
        return [
            'placement' => ['required', Rule::in(KnowledgePageNavItem::PLACEMENTS)],
            'label' => ['required', 'string', 'max:150'],
            'url' => ['required', 'string', 'max:2048', new SafePageUrl],
            'target' => ['required', Rule::in(KnowledgePageNavItem::TARGETS)],
            'icon_key' => ['nullable', Rule::in(KnowledgePageNavItem::ICON_KEYS)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }
}
