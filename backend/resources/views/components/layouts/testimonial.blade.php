@props(['title' => 'Testimoni'])
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <style>
        @font-face {
            font-family: 'Canopee';
            src: url('/fonts/Canopee.woff2') format('woff2');
            font-display: swap;
        }

        @font-face {
            font-family: 'Editorial New';
            src: url('/fonts/EditorialNew.woff2') format('woff2');
            font-display: swap;
        }

        .f-display {
            font-family: 'CabinetGrotesk', Impact, 'Arial Narrow', sans-serif;
        }

        .f-body {
            font-family: 'Editorial New', Georgia, 'Times New Roman', serif;
        }
    </style>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-white text-[#0E0E0F] antialiased">
    <main class="mx-auto max-w-2xl px-6 py-12 sm:py-20">
        {{ $slot }}
    </main>
    <p class="pb-10 text-center text-[11px] uppercase tracking-[0.2em] text-slate-300">{{ config('app.name') }}</p>
</body>

</html>