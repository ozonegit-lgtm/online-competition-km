# KM CRUD / Storage blocker resolution — 7 September 2026

ผล: Competition Admin KM CRUD, Super Admin KM CRUD และ Storage lifecycle **READY** ตามขอบเขต 4 blockers ที่ตรวจพบ พร้อมเริ่ม Public Detail ในรอบถัดไป (**YES**)

## A. Root Causes

1. **Legacy attachment:** DB จริงมี `knowledge_item_files` 1 record ของ KM ID 9 แต่ CRUD/UI/private route ใช้ `knowledge_items.attachment_path` เท่านั้น ไฟล์เดิมอยู่บน public disk และการลบ parent อาจ cascade child row โดยไม่ลบ physical file
2. **Migration drift:** DB มีประวัติ `2026_08_31_000002_expand_knowledge_management` แต่ repository ไม่มีไฟล์นั้น จึงสร้าง fresh schema ที่ขาดตาราง/คอลัมน์ของ expansion
3. **AJAX:** ฟอร์ม Competition Admin index/show ไม่กำหนด target จึง fetch สำเร็จและแสดง toast แต่ไม่เปลี่ยน DOM การลบจาก detail ก็ไม่ได้เปลี่ยนหน้า
4. **Cleanup:** helper ของทั้งสอง CRUD controllers ไม่ตรวจ boolean จาก `Storage::delete()` และไม่ได้จัดการ exception หลัง DB commit อย่างชัดเจน

## B. Files Changed

รายการนี้เป็นการแก้ในรอบปิด blockers เท่านั้น working tree มีงานเดิมอยู่ก่อนแล้ว

| ไฟล์ | การเปลี่ยนแปลง |
|---|---|
| [CompetitionAdmin/KnowledgeItemController.php](../app/Http/Controllers/CompetitionAdmin/KnowledgeItemController.php) | ใช้ cleanup service, ป้องกันแก้ไข/ลบ legacy ที่ยังย้ายไม่ครบ, คงตัวกรองเมื่อ AJAX delete จาก list |
| [SuperAdmin/KnowledgeItemController.php](../app/Http/Controllers/SuperAdmin/KnowledgeItemController.php) | ใช้ cleanup service และป้องกันแก้ไข/ลบ legacy ที่ยังย้ายไม่ครบ |
| [KnowledgeFileCleanup.php](../app/Services/KnowledgeFileCleanup.php) | ตรวจ false/exception และ log path ที่ต้อง retry |
| [LegacyKnowledgeAttachments.php](../app/Services/LegacyKnowledgeAttachments.php) | ตรวจ legacy rows ก่อน destructive CRUD |
| [ImportLegacyKnowledgeAttachments.php](../app/Console/Commands/ImportLegacyKnowledgeAttachments.php) | คำสั่ง dry-run/execute สำหรับย้ายไฟล์พร้อมตรวจ hash และรองรับ retry |
| [Reconciliation migration](../database/migrations/2026_09_07_000001_reconcile_knowledge_management_schema.php) | เติม schema ตาม DDL จริงโดยไม่แก้ migration เก่า |
| [competition-admin/km/index.blade.php](../resources/views/competition-admin/km/index.blade.php) | เพิ่ม targets สำหรับรายการและจำนวนรวม |
| [competition-admin/km/show.blade.php](../resources/views/competition-admin/km/show.blade.php) | เพิ่ม detail target และ redirect หลัง delete |
| [components/ajax-form.blade.php](../resources/views/components/ajax-form.blade.php) | เพิ่ม optional redirect prop |
| [ajax-form.js](../resources/js/ajax-form.js) | รองรับหลาย targets และ explicit redirect หลังสำเร็จ |
| [KnowledgeItemAjaxTest.php](../tests/Feature/KnowledgeItemAjaxTest.php) | ตรวจ rendered targets, สถานะ, ปุ่ม, redirect และการคงตัวกรอง |
| [KnowledgeItemCleanupFailureTest.php](../tests/Feature/KnowledgeItemCleanupFailureTest.php) | false/exception ใน create rollback, replacement, remove และ destroy ของทั้งสอง roles |
| [KnowledgeManagementReconciliationTest.php](../tests/Feature/KnowledgeManagementReconciliationTest.php) | fresh schema, constraints และการรันซ้ำโดยไม่เสียข้อมูล |
| [LegacyKnowledgeAttachmentMigrationTest.php](../tests/Feature/LegacyKnowledgeAttachmentMigrationTest.php) | dry-run, migration, private visibility, cleanup, conflicts, missing/corrupt files, DB/copy/delete failures และ symlink |
| [km-ajax.test.mjs](../tests/Browser/km-ajax.test.mjs) | Chrome DOM/fetch regression บน HTTP fixture แยกจากข้อมูลจริง |
| [รายงานฉบับนี้](km-crud-storage-blockers-2026-09-07.md) | ผลตรวจและขั้นตอนใช้งาน |

Production assets ถูก build แล้วใน `public/build` ซึ่งเป็น generated output ไม่รวมเป็น source changes ข้างต้น สคริปต์ตรวจสอบชั่วคราวถูกนำออกหลังใช้งาน

## C. Legacy Attachment Resolution

**Source of truth:** `knowledge_items.attachment_path` และ `attachment_original_name`

ก่อน:

- KM ID 9: `attachment_path = null`
- Legacy row ID 1: `test_upload.doc`
- public path: `knowledge-items/9/files/gfglNs5reHjeHvaerFHJlvDwzNUFZMXJgWN5J0o4.doc`

หลัง:

- KM ID 9 อ้างถึง `knowledge-items/attachments/legacy-1-c5ca251bb74d45102b92d3d2bc29bad56db8651d2003db1d9e45466165e0079f.doc`
- ชื่อดาวน์โหลดคงเป็น `test_upload.doc`
- ไฟล์อยู่บน local/private disk, owner UID 33 (`www-data`), mode `0600`
- ขนาด 19,456 bytes; SHA-256 `29531876925e4d3c7145965feb92a25cf8bee4c4823d2e066a642660116daccd` ตรงกันทั้งต้นฉบับ สำเนา private และข้อมูลที่ดาวน์โหลดผ่าน HTTP
- เก็บ parent reference สำเร็จก่อนลบ source และ retire child row; ปัจจุบัน legacy rows = **0**, KM = **10**, canonical attachments = **2**
- `GET /knowledge-items/9/attachment` → **200**, `application/msword`, `attachment; filename=test_upload.doc`, `Cache-Control: no-store, private`
- URL `/storage/...` เดิม → **403** และไม่ส่งไฟล์; physical public source ไม่มีแล้ว
- รูปปก KM 9 และ attachment KM 11 ยังตอบ **200**

Snapshot ก่อนเปลี่ยนข้อมูลอยู่บน private disk ที่ `knowledge-items-maintenance/km-blockers-20260907-065844.json` มี metadata เดิมและ hash สำหรับตรวจสอบย้อนหลัง ไม่เปิดผ่าน public route

คำสั่งจะตรวจทุก record ก่อนเริ่มเขียน หากพบหลาย attachments ใน parent เดียวหรือชนกับ canonical attachment ปัจจุบัน จะหยุดโดยไม่เลือกทิ้งไฟล์ใด กรณีข้อมูลจริงรอบนี้มีเพียง 1 ไฟล์และย้ายครบแล้ว

เมื่อ source cleanup ล้มเหลว จะเก็บ legacy row ไว้เป็น reference สำหรับ retry พร้อมส่ง exit code failure; update/delete ของ parent ถูกป้องกันจนย้ายครบ การรันซ้ำหลังสำเร็จตรวจพบ 0 รายการและไม่เปลี่ยนข้อมูลเพิ่ม

## D. Migration Resolution

เพิ่ม `2026_09_07_000001_reconcile_knowledge_management_schema` โดยอิง schema จริง:

- `knowledge_categories` พร้อม columns, unique slug และ active index
- `knowledge_items.knowledge_type`, `knowledge_category_id`, `external_url`, `archived_at` พร้อม default/index/FK ที่เกี่ยวข้อง
- `status` เปลี่ยน enum เดิมเป็น varchar(20) เฉพาะกรณีที่ยังเป็น enum
- `knowledge_item_files` พร้อม metadata, composite index และ cascade FK

**Fresh DB:** ทดสอบผ่านทั้ง SQLite ใน feature suite และ MariaDB ชั่วคราวซึ่งรัน migrations จาก repository ทั้งชุด columns/indexes/foreign keys ของทั้ง 3 ตารางตรงกับ DB ที่ใช้งานจริง ฐานข้อมูล MariaDB ทดสอบถูกลบแล้ว

**Existing DB:** รัน reconciliation สำเร็จ ข้อมูลเดิมอยู่ครบ ประวัติ migration ที่ไฟล์หายยังคงอยู่และไม่ถูกเขียนทับ

**Repeatability:** ทดสอบ up ซ้ำและ down แล้วข้อมูลยังอยู่ `down()` ตั้งใจไม่ลบ schema ที่อาจมีอยู่ก่อน migration นี้ หากต้องย้อน schema ต้องออก migration ใหม่ที่ประเมินข้อมูลแล้ว ไม่ใช้ rollback เพื่อลบ expansion เดิม

การทดสอบระหว่างพัฒนาพบว่า SQLite rebuild ที่ไม่จำเป็นอาจ cascade child rows จึงเพิ่มการตรวจ enum ก่อนเปลี่ยน column; regression test ที่มี legacy row ยืนยันว่ารันซ้ำไม่ทำข้อมูลหาย

## E. AJAX Resolution

- **Publish:** list/detail เปลี่ยน badge เป็น published และแสดงปุ่ม unpublish จาก HTML response
- **Unpublish:** list/detail เปลี่ยน badge เป็น draft และแสดงปุ่ม publish
- **Delete จาก list:** เปลี่ยน `#km-list` และ `#km-total`, card หายและจำนวนรวมถูกต้อง คง search/status/filter URL เดิมสำหรับ AJAX request ที่มาจาก list
- **Delete จาก detail:** redirect กลับ list ตาม optional component prop
- ใช้ delegated listener เดิม จึงใช้งานปุ่มที่เพิ่งแทนที่ได้ทันที ไม่มี framework ใหม่หรือ redesign

Chrome ทดสอบ fetch/DOM/redirect จริงกับ HTTP fixture; feature test ตรวจ Blade และ Laravel actions จริงบนฐานข้อมูลทดสอบแยก การตรวจ Chrome ไม่ได้ใช้การกดลบข้อมูลจริงในระบบ

## F. Cleanup Safety

- **Create failure:** cleanup uploads ที่เขียนสำเร็จแล้ว และโยน original upload/DB exception ต่อไป
- **Update failure ก่อน commit:** cleanup เฉพาะไฟล์ใหม่ตาม lifecycle เดิม
- **Replacement/remove หลัง commit:** ลบไฟล์เก่าของ KM เท่านั้น ไม่ลบไฟล์ต้นทาง Submission
- **Destroy:** commit การลบ DB ก่อน แล้ว cleanup cover/attachment
- **Storage delete false/exception:** `KnowledgeFileCleanup` บันทึก ERROR `KM file cleanup failed; retry required` พร้อม disk, relative path และ exception ไม่ throw storage failure ไปย้อน DB ที่ commit แล้ว
- มี regression tests ยืนยันว่าทั้งสอง roles บันทึก DB สำเร็จแม้ cleanup fail และกรณี create fail ยังคง original exception

Strategy นี้ใช้ log สำหรับติดตามและ retry โดยผู้ดูแล ไม่ใช่ automatic retry queue เมื่อเกิด storage failure ต้องแก้สิทธิ์/พื้นที่/การเชื่อมต่อ ตรวจว่า path ไม่ถูกอ้างอิงใน `knowledge_items.cover_image` หรือ `attachment_path` แล้วจึง retry cleanup ผ่าน service ห้ามลบไฟล์เพียงเพราะพบชื่อใน log โดยไม่ตรวจ reference ปัจจุบัน

ไฟล์ที่ลบไม่สำเร็จอาจค้างทางกายภาพจน retry แต่ failure จะมีหลักฐานตรวจสอบได้ การย้ายข้อมูลจริงครั้งนี้ไม่มี cleanup failure และไม่มี orphan เพิ่ม

## G. Tests and Verification

```text
docker compose exec -T --user www-data app php artisan test --do-not-cache-result --filter 'Knowledge|SubmissionFileAccessTest'
120 passed, 0 failed, 754 assertions

node --test tests/Browser/km-ajax.test.mjs
7 passed, 0 failed (1 parent test + 6 browser behavior subtests)

docker compose exec -T node npm run build
PASS — Vite production build
```

PHP suite ครอบคลุม CompetitionAdminKnowledgeItemCrudTest, SuperAdminKnowledgeItemCrudTest, KnowledgeItemPolicyTest, KnowledgeItemUploadTest, KnowledgeItemStorageWriteFailureTest, KnowledgeItemFileAccessTest, KnowledgeManagementPublicationTest, KnowledgeItemAttachmentSchemaTest, StandaloneKnowledgeItemSchemaTest, SecureKnowledgeItemFilesCommandTest, SubmissionFileAccessTest และ tests ใหม่ 4 classes พร้อม homepage smoke test

ตรวจเพิ่มจริง:

- Fresh MariaDB schema เทียบ columns/indexes/FKs ทั้ง 3 ตาราง: PASS
- Existing migration และ importer execute/retry: PASS
- HTTP ดาวน์โหลดจริงและ SHA-256: PASS
- Route list: private routes เดิมยังอยู่
- Git diff/check: ขอบเขต source changes ตามรายการ B ไม่มี whitespace error; มีคำเตือน CRLF ของไฟล์ Submission เดิมที่ไม่ได้แก้ในรอบนี้
- Laravel log: พบ error ของสคริปต์ตรวจสอบชั่วคราว `ksort()` ระหว่างพัฒนา ซึ่งแก้และรันผ่านแล้ว ไม่พบ KM runtime/cleanup error ใหม่หลังรัน migration และ HTTP verification

## H. Logic Safety

รอบนี้ไม่แก้ Public KM query, ranking, search/filter query, score logic, Submission logic, authorization policy หรือ private file URL contract

ตรวจเปรียบเทียบข้อมูลก่อน/หลังแล้ว `knowledge_categories`, `submissions`, `submission_files`, `scores`, `competitions` และทุกฟิลด์ KM นอกเหนือจาก attachment fields คงเดิม เฉพาะ KM ID 9 ได้รับ canonical attachment reference

`resources/views/show.blade.php` ของ Public ไม่ได้สร้างหรือแก้ไขในรอบนี้ งานและไฟล์ที่แก้ค้างไว้ก่อนหน้าใน working tree ยังคงอยู่

## I. Remaining Issues

**Critical ในขอบเขต 4 blockers:** ไม่พบรายการค้าง

**Minor / operational:**

- ยังมีไฟล์ private ที่ไม่มี reference อยู่เดิม 8 ไฟล์ (covers 4, attachments 4) ตรงกับก่อนแก้ ไม่ได้ลบเพราะยังไม่ยืนยันที่มา การย้ายครั้งนี้ไม่เพิ่มจำนวนนี้
- Cleanup failure ในอนาคตต้องมีผู้ดูแลตรวจ log และ retry หลังแก้ storage; ไม่มี background retry queue ในขอบเขตรอบนี้
- Importer ตั้งใจปฏิเสธหลาย legacy attachments ต่อ parent หรือข้อมูลชนกัน เพื่อป้องกันการทิ้งไฟล์ หากนำ DB ชุดอื่นเข้ามาต้องแก้ conflict ให้ครบก่อนถือว่าย้ายเสร็จ

## J. Final Status

| งาน | สถานะ |
|---|---|
| Competition Admin KM CRUD | **READY** |
| Super Admin KM CRUD | **READY** |
| Storage lifecycle ตามขอบเขต audit | **READY** |
| พร้อมเริ่ม Public Detail ในรอบถัดไป | **YES** |

## Deployment / retry procedure

สำหรับ environment อื่น ให้สำรอง DB/ไฟล์และหยุด KM writes ระหว่างย้าย:

```bash
php artisan down
php artisan migrate --force
php artisan knowledge-items:import-legacy-attachments
php artisan knowledge-items:import-legacy-attachments --execute
php artisan up
```

ตรวจ exit code ทุกคำสั่ง หาก importer fail ให้ดูข้อความและแก้สาเหตุก่อนยืนยันการย้ายเสร็จ public legacy source ที่ cleanup ไม่สำเร็จอาจยังเข้าถึงทาง static URL ได้ ต้องตรวจและรันซ้ำจนสำเร็จ ส่วน environment ปัจจุบันได้รันครบและเปิดระบบแล้ว
