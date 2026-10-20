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
        <div
            class="flex h-16 items-center bg-gradient-to-r from-pink-500 to-purple-500 px-6 text-lg font-bold text-white">
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
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between bg-white/50 backdrop-blur-sm px-4 shadow-sm lg:px-8">
            <button class="lg:hidden" @click="open = !open" aria-label="Menu">☰</button>
            <div class="hidden text-sm text-gray-500 lg:block">{{ $title }}</div>
            <div class="flex items-center gap-4 text-sm">
                <span>{{ auth()->user()->name }}</span>
                {{-- <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="text-gray-500 hover:text-gray-900">Keluar</button>
                </form> --}}
            </div>
        </header>

        <main class="p-4 lg:p-8">{{ $slot }}</main>
    </div>
</body>

</html>