{{-- resources/views/livewire/finance/transfer-manager.blade.php --}}
@php
    $rp = fn($n) => ($n < 0 ? '−' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    $in = 'mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500';
@endphp
<div class="space-y-5">
    @if(session('done'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('done') }}</div>
    @endif

    <x-erp.card>
        <h3 class="text-base font-semibold text-slate-900">Transfer antar akun</h3>
        <p class="mt-1 text-sm text-slate-500">Memindahkan uang tidak dihitung sebagai pemasukan atau pengeluaran.
            Biaya admin (kalau ada) dicatat sebagai pengeluaran.</p>

        <form wire:submit="save" class="mt-5 space-y-4">
            <div class="grid items-end gap-3 sm:grid-cols-[1fr_auto_1fr]">
                <div>
                    <label class="text-sm font-medium text-slate-700">Dari</label>
                    <select wire:model.live="fromId" class="{{ $in }}">
                        <option value="">Pilih akun…</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} · {{ $rp($balances[$a->id]) }}</option>
                        @endforeach
                    </select>
                    @error('fromId')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="button" wire:click="swap" title="Tukar akun asal dan tujuan"
                    class="mx-auto flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600">⇄</button>

                <div>
                    <label class="text-sm font-medium text-slate-700">Ke</label>
                    <select wire:model.live="toId" class="{{ $in }}">
                        <option value="">Pilih akun…</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} · {{ $rp($balances[$a->id]) }}</option>
                        @endforeach
                    </select>
                    @error('toId')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-slate-700">Nominal</label>
                    <input wire:model.live.debounce.400ms="amount" placeholder="500rb" class="{{ $in }} tabular-nums">
                    @error('amount')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Biaya admin <span
                            class="font-normal text-slate-400">(opsional)</span></label>
                    <input wire:model.live.debounce.400ms="fee" placeholder="2500" class="{{ $in }} tabular-nums">
                    @error('fee')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Tanggal</label>
                    <input type="date" wire:model="date" max="{{ today()->toDateString() }}" class="{{ $in }}">
                    @error('date')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <input wire:model="note" placeholder="Catatan (opsional)" class="{{ $in }}">

            @if($preview)
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Saldo setelah transfer</p>
                    <div class="mt-2 grid gap-1 sm:grid-cols-2">
                        <div class="flex">
                            <p class="flex justify-between gap-3"><span
                                    class="text-slate-500">{{ $accounts->firstWhere('id', $fromId)?->name }}</span>
                                <span
                                    class="font-medium tabular-nums {{ $preview['from'] < 0 ? 'text-rose-600' : '' }}">{{ $rp($preview['from']) }}</span>
                            </p>
                        </div>
                        <div class="flex">
                            <p class="flex justify-between gap-3"><span
                                    class="text-slate-500">{{ $accounts->firstWhere('id', $toId)?->name }}</span>
                                <span class="font-medium tabular-nums">{{ $rp($preview['to']) }}</span>
                            </p>
                        </div>
                    </div>
                    @if($preview['from'] < 0)
                        <p class="mt-2 text-xs text-rose-600">Saldo akun asal akan minus. Kalau saldo di web tidak sama dengan
                            m-banking, lakukan Sync saldo dulu.</p>
                    @endif
                </div>
            @endif

            <div class="flex justify-end">
                <button wire:loading.attr="disabled" wire:target="save"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">Transfer</button>
            </div>
        </form>
    </x-erp.card>

    <x-erp.card class="p-0">
        <div class="border-b border-slate-100 px-5 py-4 text-sm font-medium text-slate-800">Riwayat transfer</div>
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-800 text-[11px] font-medium uppercase tracking-wider text-white">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Dari → Ke</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-right">Biaya</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $r)
                        <tr wire:key="tf-{{ $r->id }}">
                            <td class="px-4 py-3 text-slate-500">{{ $r->transacted_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $r->fromAccount->name }} <span
                                    class="text-slate-400">→</span> {{ $r->toAccount->name }}
                                @if($r->source !== 'web')<span
                                class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-normal text-slate-500">{{ $r->source }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums">{{ $rp($r->amount) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $r->fee ? $rp($r->fee) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->note }}</td>
                            <td class="px-4 py-3 text-right text-xs">
                                <button wire:click="delete({{ $r->id }})"
                                    wire:confirm="Hapus transfer ini? Saldo kedua akun kembali seperti sebelum transfer, dan biaya admin ikut dihapus."
                                    class="text-slate-400 hover:text-rose-600">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada transfer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $rows->links() }}</div>
    </x-erp.card>
</div>