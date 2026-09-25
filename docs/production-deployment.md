# Production deployment: Hostinger VPS

เอกสารนี้ใช้กับ `docker-compose.prod.yml` เท่านั้น ส่วน `docker-compose.yml` ยังคงเป็นชุด Local Development เดิม

## Architecture

```text
Cloudflare (เพิ่มภายหลัง)
    -> Hostinger VPS :80
        -> Nginx container
            -> PHP-FPM / Laravel container :9000 (Docker network only)
                -> MariaDB :3306 (internal Docker network only)
```

Production ไม่มี phpMyAdmin, Vite dev server, Node runtime หรือ host port สำหรับ MariaDB โดย frontend assets ถูก build เข้า image ตั้งแต่ขั้นตอน Docker build

## 1. เตรียม VPS และ repository

ติดตั้ง Docker Engine, Docker Compose plugin และ Git ก่อน จากนั้น:

```bash
git clone <REPOSITORY_URL> online-competition-km
cd online-competition-km
cp .env.production.example .env.production
chmod 600 .env.production
```

แก้ `.env.production` และอย่างน้อยต้องกำหนดค่าต่อไปนี้:

- `APP_URL`
- `DB_PASSWORD` เป็นค่าสุ่มใหม่ ห้ามใช้ค่า Local
- `DB_ROOT_PASSWORD` เป็นค่าสุ่มใหม่และต้องต่างจาก `DB_PASSWORD`
- `SUPER_ADMIN_PASSWORD` อย่างน้อย 12 ตัวอักษร สำหรับการ seed ครั้งแรกเท่านั้น

ควรครอบรหัสผ่านที่มี `$`, `#`, ช่องว่าง หรืออักขระพิเศษด้วย single quote ในไฟล์ env ห้ามนำ `.env.production` เข้า Git

## 2. สร้าง APP_KEY บน Server

กรอกค่า DB ใน `.env.production` ให้ครบก่อน แล้วใช้ production image สร้าง key ลงไฟล์บน host โดยตรง:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml run --rm --no-deps --build \
  -e APP_OPTIMIZE=false \
  -e APP_KEY= \
  -v "$PWD/.env.production:/var/www/html/.env" \
  app php artisan key:generate --force

grep -q '^APP_KEY=base64:' .env.production
```

คำสั่ง `grep -q` ตรวจผลโดยไม่พิมพ์ key ออกหน้าจอ ห้ามสร้าง key ใหม่ในระบบที่มีข้อมูลใช้งานแล้ว เว้นแต่ได้วางแผนหมุน key และผลกระทบต่อ encrypted data/session ไว้แล้ว

## 3. Validate และเริ่มระบบ

ทุกคำสั่ง production ต้องใส่ `--env-file .env.production` เพราะค่าฐานข้อมูลใน Compose ถูก interpolate ก่อนสร้าง container:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml config --quiet
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.production -f docker-compose.prod.yml ps
```

จากนั้น migrate และ optimize:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan optimize
```

เฉพาะฐานข้อมูลใหม่ ให้ seed ข้อมูลตั้งต้นและ Super Admin หนึ่งครั้ง:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan db:seed --force
```

เมื่อสร้าง Super Admin สำเร็จแล้ว ให้ตั้ง `SUPER_ADMIN_PASSWORD=` กลับเป็นค่าว่างและ recreate app เพื่อลบรหัสผ่านเริ่มต้นออกจาก environment ของ container:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --force-recreate app
```

ไม่ควรรัน `db:seed` อัตโนมัติทุก deploy ปัจจุบัน seeder เหมาะสำหรับ bootstrap ฐานข้อมูลใหม่ และ Super Admin ที่มีอยู่จะไม่ถูกเปลี่ยนรหัสผ่าน

ตรวจ health endpoint:

```bash
curl -fsS http://127.0.0.1/up
docker compose --env-file .env.production -f docker-compose.prod.yml ps
```

ก่อนมี HTTPS ให้ใช้ `/up` สำหรับ smoke test หากจำเป็นต้องทดสอบ login/form ผ่าน HTTP ชั่วคราว ต้องตั้ง `APP_URL=http://VPS_IP` และ `SESSION_SECURE_COOKIE=false` ชั่วคราว แล้วเปลี่ยนกลับเป็น HTTPS/`true` ก่อนเปิดให้ผู้ใช้จริง

## 4. Persistent data และ backup targets

Compose สร้าง named volumes ต่อไปนี้:

- `online-competition-km-prod_db_data` -> MariaDB `/var/lib/mysql`
- `online-competition-km-prod_storage_data` -> Laravel `/var/www/html/storage`

ตำแหน่งสำคัญภายใน storage volume:

- Submission files -> `app/private/submissions/`
- KM covers/attachments -> `app/private/knowledge-items/`
- Knowledge Page images -> `app/private/knowledge-page/assets/`
- Public profile/competition/template images -> `app/public/`
- File sessions/cache/logs -> `framework/` และ `logs/`

`docker compose down`, `up`, rebuild หรือ replace container ตามปกติจะไม่ลบ named volume แต่ `docker compose down -v` จะลบข้อมูล จึงห้ามใช้ `-v` โดยไม่มี backup

ฐานข้อมูลควร backup แบบ logical dump ขณะระบบทำงาน แทนการ copy raw volume ตัวอย่าง:

```bash
mkdir -p backups
backup_stamp="$(date +%Y%m%d-%H%M%S)"
docker compose --env-file .env.production -f docker-compose.prod.yml exec -T db sh -c \
  'exec mariadb-dump --single-transaction --quick --lock-tables=false -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' \
  > "backups/database-$backup_stamp.sql"
```

สำหรับ uploaded files ให้ archive volume `online-competition-km-prod_storage_data` ด้วยเครื่องมือ backup ของ VPS หรือ maintenance container และทดสอบ restore เป็นระยะ งานรอบนี้ยังไม่ได้ตั้ง scheduled backup อัตโนมัติ

ตัวอย่างการ archive storage volume:

```bash
backup_stamp="$(date +%Y%m%d-%H%M%S)"
docker run --rm \
  -v online-competition-km-prod_storage_data:/source:ro \
  -v "$PWD/backups:/backup" \
  alpine:3.21 tar -czf "/backup/storage-$backup_stamp.tar.gz" -C /source .
```

ไฟล์ upload เดิมจากเครื่อง Local ถูก ignore โดย Git และจะไม่ถูกส่งขึ้น VPS จาก `git clone` หากต้องย้ายข้อมูลเดิม ต้องส่ง backup ของฐานข้อมูลและ `storage/app` แยกจาก repository แล้ว restore เข้า production volumes ก่อนเปิดระบบ

## 5. อัปเดต deployment

```bash
git pull --ff-only
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan optimize
docker compose --env-file .env.production -f docker-compose.prod.yml ps
```

## 6. Existing/legacy uploaded files

ระบบปัจจุบันเขียน Submission และ KM files ใหม่ลง private disk อยู่แล้ว หากย้ายข้อมูลมาจาก installation เก่า ให้ backup ฐานข้อมูลและ storage ก่อน แล้วตรวจแบบ dry run:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan submissions:secure-files
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan knowledge-items:secure-files
```

ระบบ legacy attachment ถูกยกเลิกหลังตรวจยืนยันว่าไม่มีข้อมูลค้างแล้ว ไฟล์ KM ปัจจุบันยังคงอ้างอิงผ่าน `knowledge_items.attachment_path`

## 7. ขั้นตอนหลังระบบ HTTP ทำงาน

- ตั้ง Domain และ Cloudflare DNS
- ตั้ง HTTPS ระหว่าง Cloudflare กับ origin และเปลี่ยน `APP_URL` เป็น URL จริง
- จำกัด firewall ให้เปิดเฉพาะ port ที่จำเป็น และทำ SSH hardening
- ตั้ง trusted proxy/real client IP สำหรับ Cloudflare ทั้ง Nginx และ Laravel ก่อนพึ่งพา IP rate limit/audit log
- ตั้ง backup ฐานข้อมูลและ storage volume พร้อมทดสอบ restore
- ทดสอบ upload/download, authorization, login, submission และ KM บน production จริง
