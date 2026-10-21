<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-sky-100 font-sans text-slate-700 antialiased">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="mb-6 flex items-center justify-center gap-3">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 text-lg font-bold text-white shadow-lg shadow-blue-500/30">D</span>
                <span class="text-lg font-semibold text-slate-900">{{ config('app.name') }}</span>
            </div>
            <div class="rounded-2xl bg-white p-8 shadow-[0_10px_40px_rgba(37,99,235,0.12)]">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>