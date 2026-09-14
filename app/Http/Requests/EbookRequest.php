<?php

namespace App\Http\Requests;

use App\Models\KnowledgeItem;
use App\Rules\KnowledgeItemFilePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true
            && $this->user()?->role?->role_name === 'Super Admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:65535'],
            'content' => ['nullable', 'string'],
            'knowledge_category_id' => ['required', 'integer', 'exists:knowledge_categories,id'],
            'cover_image' => ['nullable', 'file', 'max:10240', KnowledgeItemFilePolicy::cover()],
            'attachment' => ['nullable', 'file', 'max:10240', 'extensions:pdf', KnowledgeItemFilePolicy::attachment()],
            'external_url' => ['nullable', 'url:http,https', 'max:2048'],
            'publication_year' => ['nullable', 'integer', 'between:1,65535'],
            'volume' => ['nullable', 'string', 'max:50'],
            'issue' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_attachment' => ['nullable', 'boolean'],
            'knowledge_type' => ['prohibited'],
            'submission_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'category_id' => ['prohibited'],
            'status' => ['prohibited'],
            'published_at' => ['prohibited'],
            'attachment_path' => ['prohibited'],
            'attachment_original_name' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $routeId = $this->route('ebook');
            $ebook = is_numeric($routeId)
                ? KnowledgeItem::query()->ebooks()->find((int) $routeId)
                : null;
            $keepsExistingPdf = $ebook?->attachment_path && ! $this->boolean('remove_attachment');
            $hasExternalUrl = $this->exists('external_url')
                ? $this->filled('external_url')
                : filled($ebook?->external_url);

            if (! $this->hasFile('attachment')
                && ! $keepsExistingPdf
                && ! $hasExternalUrl) {
                $validator->errors()->add(
                    'attachment',
                    'ต้องมีไฟล์ PDF หรือ External URL อย่างน้อยหนึ่งอย่าง'
                );
            }
        });
    }
}
