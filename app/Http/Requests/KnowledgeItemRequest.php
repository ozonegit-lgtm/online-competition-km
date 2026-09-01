<?php

namespace App\Http\Requests;

use App\Rules\KnowledgeItemFilePolicy;
use Illuminate\Foundation\Http\FormRequest;

class KnowledgeItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                'exists:competition_categories,id',
            ],
            'summary' => ['nullable', 'string', 'max:65535'],
            'content' => ['nullable', 'string'],
            'cover_image' => [
                'nullable',
                'file',
                'max:10240',
                KnowledgeItemFilePolicy::cover(),
            ],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                KnowledgeItemFilePolicy::attachment(),
            ],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_attachment' => ['nullable', 'boolean'],
            'created_by' => ['prohibited'],
            'submission_id' => ['prohibited'],
            'attachment_path' => ['prohibited'],
            'attachment_original_name' => ['prohibited'],
            'status' => ['prohibited'],
            'published_at' => ['prohibited'],
            'is_featured' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'กรุณาระบุชื่อองค์ความรู้',
            'title.string' => 'ชื่อองค์ความรู้ต้องเป็นข้อความ',
            'title.max' => 'ชื่อองค์ความรู้ต้องไม่เกิน 255 ตัวอักษร',
            'category_id.required' => 'กรุณาเลือกหมวดหมู่',
            'category_id.integer' => 'หมวดหมู่ไม่ถูกต้อง',
            'category_id.exists' => 'ไม่พบหมวดหมู่ที่เลือก',
            'summary.string' => 'บทสรุปต้องเป็นข้อความ',
            'summary.max' => 'บทสรุปยาวเกินกว่าที่ระบบรองรับ',
            'content.string' => 'เนื้อหาต้องเป็นข้อความ',
            'cover_image.file' => 'ภาพปกต้องเป็นไฟล์',
            'cover_image.max' => 'ภาพปกต้องมีขนาดไม่เกิน 10 MB',
            'attachment.file' => 'ไฟล์แนบต้องเป็นไฟล์',
            'attachment.max' => 'ไฟล์แนบต้องมีขนาดไม่เกิน 10 MB',
            'remove_cover_image.boolean' => 'คำสั่งลบภาพปกไม่ถูกต้อง',
            'remove_attachment.boolean' => 'คำสั่งลบไฟล์แนบไม่ถูกต้อง',
            '*.prohibited' => 'ไม่อนุญาตให้กำหนดข้อมูลควบคุมระบบนี้',
        ];
    }
}
