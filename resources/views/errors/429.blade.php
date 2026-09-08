<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งคำขอบ่อยเกินไป</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 text-slate-800 text-sm">
    <main class="w-full max-w-lg rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
        <h1 class="text-xl font-bold">ส่งคำขอบ่อยเกินไป</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            กรุณารอสักครู่แล้วลองส่งผลงานอีกครั้ง
        </p>
        <a href="{{ url()->previous() }}" class="mt-4 inline-flex rounded-xl bg-blue-600 text-sm font-semibold text-white items-center justify-center h-9 px-3">
            กลับไปยังแบบฟอร์ม
        </a>
    </main>
</body>
</html>
