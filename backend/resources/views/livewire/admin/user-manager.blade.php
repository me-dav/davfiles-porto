{{-- resources/views/livewire/admin/user-manager.blade.php --}}
<x-erp.card flush>
    <div class="flex flex-wrap gap-3 border-b border-gray-100 p-4">
        <input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email…"
            class="rounded-lg border-gray-300 text-sm">
        <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm">
            <option value="">Semua status</option>
            <option value="pending">Menunggu</option>
            <option value="active">Aktif</option>
            <option value="suspended">Ditangguhkan</option>
        </select>
    </div>

    <div class="overflow-x-auto">
        <x-erp.table>
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Modul</th>
                    <th class="px-4 py-3">Daftar</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $u)
                    @php
                        $badge = match ($u->status) {
                            'pending' => 'bg-amber-50 text-amber-700',
                            'active' => 'bg-emerald-50 text-emerald-700',
                            default => 'bg-rose-50 text-rose-700',
                        };
                    @endphp
                    <tr wire:key="u-{{ $u->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $u->name }} @if($u->isSuperAdmin())<span
                                class="ml-1 rounded bg-indigo-50 px-1.5 py-0.5 text-xs text-indigo-700">super
                            admin</span>@endif</p>
                            <p class="text-xs text-gray-500">{{ $u->email }}</p>
                        </td>
                        <td class="px-4 py-3"><span
                                class="rounded px-2 py-0.5 text-xs font-medium {{ $badge }}">{{ $u->status }}</span></td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $u->isSuperAdmin() ? 'semua' : (implode(', ', $u->modules ?? []) ?: '-') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $u->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            @unless($u->isSuperAdmin() || $u->id === auth()->id())
                                @if($u->status === 'pending')
                                    <button wire:click="approve({{ $u->id }})"
                                        wire:confirm="Aktifkan {{ $u->name }} dengan modul Finance?"
                                        class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Approve</button>
                                @elseif($u->status === 'active')
                                    <button wire:click="suspend({{ $u->id }})" wire:confirm="Tangguhkan {{ $u->name }}?"
                                        class="text-xs text-gray-400 hover:text-rose-600">Suspend</button>
                                @else
                                    <button wire:click="reactivate({{ $u->id }})"
                                        class="text-xs text-indigo-600 hover:underline">Aktifkan lagi</button>
                                @endif
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Tidak ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-erp.table>
    </div>
    <div class="p-4">{{ $users->links() }}</div>
</x-erp.card>