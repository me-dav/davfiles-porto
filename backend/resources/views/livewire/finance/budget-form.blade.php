@php
    $in = 'mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500';
    $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
    $segment = fn($on) => $on ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100';
    $selCats = $categories->whereIn('id', array_map('intval', $categoryIds))->values();
    $selAccs = $accounts->whereIn('id', array_map('intval', $accountIds))->values();
@endphp
<div>
    @if($show)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
            wire:key="budget-form-modal">
            <div class="my-8 w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="text-base font-semibold text-slate-900">{{ $editingId ? 'Ubah budget' : 'Budget baru' }}</h3>
                    <button type="button" wire:click="close" aria-label="Tutup"
                        class="text-slate-400 hover:text-slate-700">✕</button>
                </div>

                <form wire:submit="save" class="mt-5 space-y-5 text-sm">
                    {{-- nama --}}
                    <div>
                        <label class="font-medium text-slate-700">Nama budget</label>
                        <input wire:model="name" maxlength="60" placeholder="mis. Makan harian" class="{{ $in }}">
                        @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- periode --}}
                    <div>
                        <label class="font-medium text-slate-700">Periode</label>

                        @if($editingId && $periodType !== 'custom')
                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500">
                                {{ $periodType === 'weekly' ? 'Mingguan, mulai hari Senin' : 'Bulanan, mulai tanggal ' . $startDay }}.
                                Jenis periode tidak bisa diubah. Untuk periode lain, buat budget baru.
                            </p>
                        @else
                            @unless($editingId)
                                <div class="mt-1 inline-flex rounded-lg border border-slate-200 p-0.5">
                                    <button type="button" wire:click="$set('periodType','weekly')"
                                        class="rounded-md px-3 py-1.5 {{ $segment($periodType === 'weekly') }}">Mingguan</button>
                                    <button type="button" wire:click="$set('periodType','monthly')"
                                        class="rounded-md px-3 py-1.5 {{ $segment($periodType === 'monthly') }}">Bulanan</button>
                                    <button type="button" wire:click="$set('periodType','custom')"
                                        class="rounded-md px-3 py-1.5 {{ $segment($periodType === 'custom') }}">Rentang
                                        tanggal</button>
                                </div>
                            @endunless

                            @if($periodType === 'custom')
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <div>
                                        <input type="date" wire:model.live="startsOn" class="{{ $in }}">
                                        @error('startsOn')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <input type="date" wire:model.live="endsOn" class="{{ $in }}">
                                        @error('endsOn')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($periodType === 'weekly' && !$editingId)
                            <p class="mt-2 text-xs text-slate-400">Periode berjalan dari Senin sampai Minggu.</p>
                        @endif

                        @if($periodType === 'monthly' && !$editingId)
                            <div class="mt-3 flex items-center gap-3">
                                <span class="text-slate-600">Mulai tanggal</span>
                                <select wire:model.live="startDay" class="w-24 rounded-lg border-slate-300 text-sm">
                                    @for($d = 1; $d <= 28; $d++)
                                    <option value="{{ $d }}">{{ $d }}</option>@endfor
                                </select>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">Pilih tanggal gajian bila periodemu tidak dimulai tanggal 1.
                                Tanggal 29 sampai 31 tidak tersedia agar bulan pendek tidak bermasalah.</p>
                        @endif
                    </div>

                    {{-- nominal --}}
                    <div>
                        <label class="font-medium text-slate-700">Batas budget</label>
                        <input wire:model="amount" inputmode="text" placeholder="2jt" class="{{ $in }} tabular-nums">
                        @error('amount')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @if($editingId && $periodType !== 'custom')
                            <p class="mt-1 text-xs text-slate-400">Nominal baru berlaku mulai periode ini. Periode yang sudah
                                lewat tidak berubah.</p>
                        @endif
                    </div>

                    {{-- kategori --}}
                    <div x-data="{ open: false, q: '' }">
                        <label class="font-medium text-slate-700">Kategori</label>

                        <label class="mt-2 flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2">
                            <input type="checkbox" wire:model.live="allCategories"
                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Semua kategori pengeluaran
                                <span class="block text-xs text-slate-400">Kategori yang dibuat nanti otomatis ikut
                                    dihitung.</span></span>
                        </label>

                        @unless($allCategories)
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @foreach($selCats->take(3) as $c)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs text-blue-700">{{ $c->name }}</span>
                                @endforeach
                                @if($selCats->count() > 3)
                                    <span
                                        class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">+{{ $selCats->count() - 3 }}
                                        lainnya</span>
                                @endif
                                @if($selCats->isEmpty())
                                    <span class="text-xs text-slate-400">Belum ada yang dipilih.</span>
                                @endif
                                <button type="button" @click="open = !open"
                                    class="ml-auto text-xs font-medium text-blue-600 hover:underline"
                                    x-text="open ? 'Tutup daftar' : 'Pilih kategori'"></button>
                            </div>

                            <div x-show="open" x-cloak class="mt-2 rounded-lg border border-slate-200 p-3">
                                <div class="flex gap-2">
                                    <input type="search" x-model="q" placeholder="Cari kategori…"
                                        class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm">
                                    <button type="button" wire:click="selectAllCategories"
                                        class="shrink-0 text-xs font-medium text-blue-600 hover:underline">Semua</button>
                                    <button type="button" wire:click="clearCategories"
                                        class="shrink-0 text-xs text-slate-500 hover:underline">Kosongkan</button>
                                </div>
                                <div class="mt-2 grid max-h-48 grid-cols-2 gap-1 overflow-y-auto sm:grid-cols-3">
                                    @foreach($categories as $c)
                                        <label data-n="{{ mb_strtolower($c->name) }}"
                                            x-show="!q || $el.dataset.n.includes(q.toLowerCase())"
                                            class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 hover:bg-slate-50">
                                            <input type="checkbox" wire:model.live="categoryIds" value="{{ $c->id }}"
                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="truncate">{{ $c->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endunless
                        @error('categoryIds')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- akun --}}
                    <div>
                        <label class="font-medium text-slate-700">Akun</label>

                        <label class="mt-2 flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2">
                            <input type="checkbox" wire:model.live="allAccounts"
                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Semua akun
                                <span class="block text-xs text-slate-400">Akun yang dibuat nanti otomatis ikut
                                    dihitung.</span></span>
                        </label>

                        @unless($allAccounts)
                            <div class="mt-2 grid grid-cols-2 gap-1 sm:grid-cols-3">
                                @foreach($accounts as $a)
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 hover:bg-slate-50">
                                        <input type="checkbox" wire:model.live="accountIds" value="{{ $a->id }}"
                                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="truncate">{{ $a->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endunless
                        @error('accountIds')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- petunjuk dari data nyata --}}
                    @if($hint)
                        <div class="rounded-lg bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-900">
                            <p class="font-medium">Petunjuk dari datamu</p>
                            @if($hint['total90'] === 0)
                                <p class="mt-1">Belum ada pengeluaran yang cocok dalam 90 hari terakhir.</p>
                            @else
                                <p class="mt-1">Rata-rata {{ $rp($hint['per_day']) }} per hari (90 hari
                                    terakhir)@if($hint['estimate']), kira-kira {{ $rp($hint['estimate']) }} per periode @endif.</p>
                            @endif
                            @if($hint['current'] !== null)
                                <p>Periode ini sejauh ini: {{ $rp($hint['current']) }}.</p>
                            @endif
                        </div>
                    @endif

                    @if($editingId)
                        <p class="text-xs text-slate-400">Perubahan kategori dan akun berlaku juga untuk periode berjalan.</p>
                    @endif

                    <div class="flex justify-end gap-3 pt-1">
                        <button type="button" wire:click="close"
                            class="text-sm text-slate-500 hover:text-slate-800">Batal</button>
                        <button wire:loading.attr="disabled" wire:target="save"
                            class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>