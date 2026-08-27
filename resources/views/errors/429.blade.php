<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งคำขอบ่อยเกินไป</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 text-slate-800">
    <main class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <h1 class="text-2xl font-bold">ส่งคำขอบ่อยเกินไป</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            กรุณารอสักครู่แล้วลองส่งผลงานอีกครั้ง
        </p>
        <a href="{{ url()->previous() }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">
            กลับไปยังแบบฟอร์ม
        </a>
    </main>
</body>
</html>
