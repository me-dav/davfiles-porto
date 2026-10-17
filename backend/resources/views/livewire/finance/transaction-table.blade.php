{{-- resources/views/livewire/finance/transaction-table.blade.php --}}
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