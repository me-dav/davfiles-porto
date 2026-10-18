{{-- resources/views/livewire/settings/api-keys.blade.php --}}
<div class="space-y-4">
    <x-erp.card>
        <form wire:submit="create" class="flex items-end gap-3">
            <div>
                <label class="text-sm text-gray-600">Nama key</label>
                <input wire:model="name" class="mt-1 block rounded-lg border-gray-300 text-sm">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Buat
                key</button>
        </form>

        @if($newToken)
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm">
                <p class="font-medium text-amber-800">Salin sekarang. Key ini tidak akan ditampilkan lagi.</p>
                <code class="mt-2 block break-all rounded bg-white p-2 text-xs">{{ $newToken }}</code>
            </div>
        @endif
    </x-erp.card>

    <x-erp.card class="p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Terakhir dipakai</th>
                    <th class="px-4 py-3">Kedaluwarsa</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tokens as $t)
                    <tr wire:key="tk-{{ $t->id }}">
                        <td class="px-4 py-3">{{ $t->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->last_used_at?->diffForHumans() ?? 'belum pernah' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->expires_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="revoke({{ $t->id }})" wire:confirm="Cabut key ini?"
                                class="text-xs text-gray-400 hover:text-rose-600">Cabut</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada key.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-erp.card>
</div>