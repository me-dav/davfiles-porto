{{-- resources/views/livewire/finance/category-manager.blade.php --}}
@php $btn = fn($on) => $on ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'; @endphp
<div class="space-y-4">
    <x-erp.card>
        <div class="flex flex-wrap items-start gap-3">
            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm">
                <button wire:click="setKind('expense')"
                    class="rounded-md px-3 py-1.5 {{ $btn($kind === 'expense') }}">Keluar</button>
                <button wire:click="setKind('income')"
                    class="rounded-md px-3 py-1.5 {{ $btn($kind === 'income') }}">Masuk</button>
            </div>

            <form wire:submit="save" class="flex flex-1 flex-wrap items-start gap-3">
                <div>
                    <input wire:model="name" placeholder="{{ $editingId ? 'Ubah nama…' : 'Kategori baru (mis. Kopi)' }}"
                        class="w-full rounded-lg border-gray-300 text-sm lg:w-64">
                    @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    {{ $editingId ? 'Update' : 'Tambah' }}
                </button>
                @if($editingId)
                    <button type="button" wire:click="cancel"
                        class="px-2 py-2 text-sm text-gray-500 hover:text-gray-800">Batal</button>
                @endif
            </form>
        </div>
        @error('general')
        <p class="mt-3 text-sm text-rose-600">{{ $message }}</p> @enderror
    </x-erp.card>

    <x-erp.card flush>
        <x-erp.table>
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-right">Dipakai</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $c)
                    <tr wire:key="cat-{{ $c->id }}">
                        <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-500">{{ $c->transactions_count }}×</td>
                        <td class="space-x-3 px-4 py-3 text-right text-xs">
                            <button wire:click="edit({{ $c->id }})" class="text-gray-500 hover:text-gray-900">Ubah</button>
                            <button wire:click="delete({{ $c->id }})" wire:confirm="Hapus kategori {{ $c->name }}?"
                                class="text-gray-400 hover:text-rose-600">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-erp.table>
    </x-erp.card>
</div>