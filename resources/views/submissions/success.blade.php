<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งผลงานสำเร็จ</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10 text-slate-800">
    <main class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-10">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </div>

        <h1 class="mt-5 text-2xl font-bold text-slate-900">ส่งผลงานสำเร็จ</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            ระบบได้รับผลงานของคุณสำหรับการแข่งขัน
            <span class="font-semibold text-slate-700">{{ $submission->competition->title }}</span>
            เรียบร้อยแล้ว
        </p>

        <div class="mt-7 rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">รหัสผลงาน</p>
            <p class="mt-2 break-all font-mono text-2xl font-bold tracking-wider text-blue-900">
                {{ $submission->submission_code }}
            </p>
        </div>

        <dl class="mt-6 divide-y divide-slate-200 rounded-2xl border border-slate-200 text-left">
            <div class="p-4">
                <dt class="text-xs font-medium text-slate-500">ชื่อผลงาน</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $submission->project_title }}</dd>
            </div>

            <div class="p-4">
                <dt class="text-xs font-medium text-slate-500">วันที่ส่ง</dt>
                <dd class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $submission->submitted_at->format('d/m/Y H:i น.') }}
                </dd>
            </div>

            <div class="p-4">
                <dt class="text-xs font-medium text-slate-500">สถานะ</dt>
                <dd class="mt-1 text-sm font-semibold text-green-700">ส่งผลงานแล้ว</dd>
            </div>
        </dl>

        <div class="mt-6 rounded-xl bg-amber-50 px-4 py-3 text-left text-xs leading-5 text-amber-800">
            กรุณาบันทึกหรือถ่ายภาพหน้าจอรหัสผลงานนี้ไว้สำหรับใช้อ้างอิงและติดตามผลภายหลัง
        </div>

        <a href="{{ route('competitions.submissions.create',$submission->competition) }}"
            class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200">
            ส่งผลงานเพิ่มเติม
        </a>
    </main>
</body>

</html>
