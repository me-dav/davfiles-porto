{{-- resources/views/livewire/finance/transaction-table.blade.php --}}
<div>
    <x-erp.card class="p-0">
        <div class="flex flex-wrap gap-3 border-b border-gray-100 p-4">
            <input wire:model.live.debounce.300ms="search" placeholder="Cari catatan…"
                class="rounded-lg border-gray-300 text-sm">
            <select wire:model.live="type" class="rounded-lg border-gray-300 text-sm">
                <option value="">Semua tipe</option>
                <option value="expense">Keluar</option>
                <option value="income">Masuk</option>
            </select>
            <select wire:model.live="category" class="rounded-lg border-gray-300 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->kind === 'income' ? 'masuk' : 'keluar' }})</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $r)
                        <tr wire:key="tx-{{ $r->id }}">
                            <td class="px-4 py-3 text-gray-500">{{ $r->transacted_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $r->category->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $r->note }}
                                @if($r->source !== 'web')<span
                                class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">{{ $r->source }}</span>@endif
                            </td>
                            <td
                                class="px-4 py-3 text-right font-medium tabular-nums {{ $r->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $r->type === 'income' ? '+' : '−' }} Rp {{ number_format($r->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="delete({{ $r->id }})" wire:confirm="Hapus transaksi ini?"
                                    class="text-xs text-gray-400 hover:text-rose-600">Hapus</button>
                                <button wire:click="edit({{ $r->id }})"
                                    class="mr-3 text-xs text-gray-500 hover:text-gray-900">Ubah</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $rows->links() }}</div>
    </x-erp.card>

    @if($editingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="edit-modal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold">Ubah transaksi</h3>

                <form wire:submit="saveEdit" class="mt-4 space-y-3 text-sm">
                    <div class="inline-flex rounded-lg border border-gray-200 p-0.5">
                        <button type="button" wire:click="$set('eType','expense')"
                            class="rounded-md px-3 py-1.5 {{ $eType === 'expense' ? 'bg-rose-50 text-rose-700' : 'text-gray-500' }}">Keluar</button>
                        <button type="button" wire:click="$set('eType','income')"
                            class="rounded-md px-3 py-1.5 {{ $eType === 'income' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-500' }}">Masuk</button>
                    </div>

                    <div>
                        <input wire:model="eAmount" class="w-full rounded-lg border-gray-300 text-sm tabular-nums"
                            placeholder="Nominal">
                        @error('eAmount')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <select wire:model="eCategoryId" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Kategori…</option>
                                @foreach($editCategories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                            @error('eCategoryId')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <select wire:model="eAccountId" class="w-full rounded-lg border-gray-300 text-sm">
                                @foreach($accounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                            </select>
                            @error('eAccountId')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <input type="date" wire:model="eDate" max="{{ today()->toDateString() }}"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('eDate')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <input wire:model="eNote" placeholder="Catatan" class="w-full rounded-lg border-gray-300 text-sm">

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancelEdit"
                            class="text-sm text-gray-500 hover:text-gray-800">Batal</button>
                        <button
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>