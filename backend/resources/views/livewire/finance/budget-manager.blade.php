@php
    $tabCls = fn($on) => $on ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600';
    $tabs = [
        'active' => ['Aktif', $active->count()],
        'finished' => ['Selesai', $finished->count()],
        'archived' => ['Diarsipkan', $archived->count()],
    ];
    $list = $tab === 'finished' ? $finished : $active;
@endphp
<div class="space-y-5">
    <x-erp.card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="-mx-1 overflow-x-auto px-1">
                <div class="flex gap-5 whitespace-nowrap text-[11px] font-medium uppercase tracking-wider">
                    @foreach($tabs as $key => [$label, $n])
                        <button wire:click="setTab('{{ $key }}')" class="border-b-2 pb-1 {{ $tabCls($tab === $key) }}">
                            {{ $label }} <span class="ml-0.5 text-slate-400">{{ $n }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <button wire:click="$dispatch('open-budget-form')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Tambah
                budget</button>
        </div>
    </x-erp.card>

    @if($tab !== 'archived')
        @if($list->isEmpty())
            <x-erp.card>
                <p class="py-8 text-center text-sm text-slate-400">
                    {{ $tab === 'finished' ? 'Belum ada budget yang selesai.' : 'Belum ada budget. Klik "Tambah budget" untuk membuat yang pertama.' }}
                </p>
            </x-erp.card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($list as $it)
                    @php $b = $it['b'];
                    $s = $it['s']; @endphp

                    <div wire:key="bm-{{ $b->id }}" class="flex">
                        <x-erp.budget-card :b="$b" :s="$s" class="w-full">
                            <div class="mt-4 flex items-center gap-4 border-t border-slate-100 pt-3 text-xs">
                                <button wire:click="toggleHistory({{ $b->id }})" class="font-medium text-blue-600 hover:underline">
                                    {{ $openId === $b->id ? 'Tutup riwayat' : 'Riwayat' }}
                                </button>
                                <button wire:click="$dispatch('open-budget-form', { id: {{ $b->id }} })"
                                    class="text-slate-500 hover:text-slate-900">Ubah</button>
                                <button wire:click="archive({{ $b->id }})"
                                    class="ml-auto text-slate-400 hover:text-amber-600">Arsipkan</button>
                                <button wire:click="delete({{ $b->id }})"
                                    wire:confirm="Hapus budget {{ $b->name }} beserta riwayatnya? Tindakan ini tidak bisa dibatalkan."
                                    class="text-slate-400 hover:text-rose-600">Hapus</button>
                            </div>
                        </x-erp.budget-card>
                    </div>

                    @if($openId === $b->id && $history)
                        <div wire:key="bh-{{ $b->id }}" class="col-span-full">
                            @include('livewire.finance.partials.budget-history', ['b' => $b, 'history' => $history])
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @else
        <div class="overflow-hidden rounded-2xl bg-white shadow-[0_4px_24px_rgba(15,23,42,0.05)]">
            @forelse($archived as $b)
                <div wire:key="ba-{{ $b->id }}"
                    class="flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100 px-5 py-4 last:border-0">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $b->name }}</p>
                        <p class="truncate text-xs text-slate-400">{{ $b->periodTypeLabel() }} · {{ $b->categoryLabel() }} ·
                            {{ $b->accountLabel() }}</p>
                    </div>
                    <div class="flex gap-4 text-xs">
                        <button wire:click="restore({{ $b->id }})"
                            class="font-medium text-blue-600 hover:underline">Pulihkan</button>
                        <button wire:click="delete({{ $b->id }})" wire:confirm="Hapus budget {{ $b->name }} permanen?"
                            class="text-slate-400 hover:text-rose-600">Hapus</button>
                    </div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada budget yang diarsipkan.</p>
            @endforelse
        </div>
    @endif

    <livewire:finance.budget-form />
</div>