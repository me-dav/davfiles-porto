@php
    $u = auth()->user();
    $initials = collect(explode(' ', trim($u->name)))->filter()->take(2)
        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
@endphp

@props(['title' => ''])
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans text-gray-900 antialiased" x-data="{ open: false }">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-60 -translate-x-full bg-white shadow-[0_0_30px_rgba(236,72,153,0.08)]
              transition-transform lg:translate-x-0" :class="{ 'translate-x-0': open }">
        <div class="flex h-16 items-center px-6 text-lg font-bold text-black">
            {{ config('app.name') }}
        </div>
        <nav class="space-y-6 px-0 py-5 text-[13px]"">
            <div>
                <x-erp.nav-link :href=" route('dashboard')" :active="request()->routeIs('dashboard')">
            Dashboard</x-erp.nav-link>
            </div>

            @if(auth()->user()->hasModule('finance'))
                <div>
                    <p class="px-3 pb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Finance</p>
                    {{-- link Transaksi, Akun, Kategori ditambah di step finance --}}
                    {{-- layouts/erp.blade.php, di bawah judul "Finance" --}}
                    <x-erp.nav-link :href="route('finance.transactions')"
                        :active="request()->routeIs('finance.transactions')">Transaksi</x-erp.nav-link>
                    <x-erp.nav-link :href="route('finance.accounts')"
                        :active="request()->routeIs('finance.accounts')">Akun</x-erp.nav-link>
                    <x-erp.nav-link :href="route('finance.transfers')"
                        :active="request()->routeIs('finance.transfers')">Transfer</x-erp.nav-link>
                    <x-erp.nav-link :href="route('finance.categories')"
                        :active="request()->routeIs('finance.categories')">Kategori</x-erp.nav-link>
                </div>
            @endif

            @can('manage-content')
                <div>
                    <p class="px-3 pb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Admin</p>
                    @php $pending = \App\Models\User::where('status', 'pending')->count(); @endphp
                    <x-erp.nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
                        Users
                        @if($pending > 0)
                            <span
                                class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $pending }}</span>
                        @endif
                    </x-erp.nav-link>
                    <x-erp.nav-link :href="route('admin.projects')"
                        :active="request()->routeIs('admin.projects')">Projects</x-erp.nav-link>
                    <x-erp.nav-link :href="route('admin.journal')"
                        :active="request()->routeIs('admin.journal')">Journal</x-erp.nav-link>
                </div>
            @endcan
        </nav>
    </aside>

    <div class="lg:pl-60">
        {{-- Topbar --}}
        <header
            class="sticky top-0 z-30 flex h-16 items-center justify-between bg-white/50 backdrop-blur-sm px-4 shadow-sm lg:px-8">
            <button class="lg:hidden" @click="open = !open" aria-label="Menu">☰</button>
            <div class="hidden text-sm text-gray-500 lg:block">{{ $title }}</div>
            <div class="relative" x-data="{ o: false }" @click.outside="o = false" @keydown.escape.window="o = false">
                <button type="button" @click="o = !o" aria-haspopup="true" :aria-expanded="o"
                    class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 transition hover:bg-slate-100">
                    @if($u->avatar_url)
                        <img src="{{ $u->avatar_url }}" alt="" referrerpolicy="no-referrer"
                            class="h-8 w-8 rounded-full object-cover">
                    @else
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-xs font-semibold text-white">{{ $initials }}</span>
                    @endif
                    <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ $u->name }}</span>
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" />
                    </svg>
                </button>

                <div x-show="o" x-cloak x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-900/5">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $u->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                    </div>
                    <a href="{{ route('profile') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50">Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-4 lg:p-8">{{ $slot }}</main>
    </div>
</body>

</html>