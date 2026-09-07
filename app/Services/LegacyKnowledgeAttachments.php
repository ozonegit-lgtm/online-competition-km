<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LegacyKnowledgeAttachments
{
    public function ensureMigrated(int $knowledgeItemId): void
    {
        if (DB::table('knowledge_item_files')->where('knowledge_item_id', $knowledgeItemId)->exists()) {
            throw ValidationException::withMessages([
                'attachment' => 'กรุณาให้ผู้ดูแลระบบย้ายไฟล์แนบเดิมให้เสร็จก่อนแก้ไขหรือลบรายการนี้',
            ]);
        }
    }
}
