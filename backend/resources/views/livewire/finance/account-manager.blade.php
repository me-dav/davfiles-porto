{{-- resources/views/livewire/finance/account-manager.blade.php --}}
<div class="space-y-4">
    <x-erp.card>
        <form wire:submit="save" class="flex flex-col gap-3 lg:flex-row lg:items-start">
            <div>
                <input wire:model="name" placeholder="Nama akun (BCA, Dompet…)"
                    class="w-full rounded-lg border-gray-300 text-sm lg:w-56">
                @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <select wire:model="type" class="rounded-lg border-gray-300 text-sm">
                <option value="cash">Tunai</option>
                <option value="bank">Bank</option>
                <option value="ewallet">E-Wallet</option>
                <option value="investment">Investasi</option>
            </select>
            <div>
                <input wire:model="opening" placeholder="Saldo awal (0, 500rb)"
                    class="w-full rounded-lg border-gray-300 text-sm tabular-nums lg:w-44">
                @error('opening')
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
        @error('general')
        <p class="mt-3 text-sm text-rose-600">{{ $message }}</p> @enderror
    </x-erp.card>

    <x-erp.card class="p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Akun</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($accounts as $a)
                    @php $bal = $a->opening_balance + (int) ($flow[$a->id] ?? 0); @endphp
                    <tr wire:key="acc-{{ $a->id }}" class="{{ $a->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3 font-medium">
                            {{ $a->name }}
                            @if($a->is_default)<span
                            class="ml-1 rounded bg-indigo-50 px-1.5 py-0.5 text-xs text-indigo-700">default</span>@endif
                            @unless($a->is_active)<span
                            class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">diarsipkan</span>@endunless
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ ['cash' => 'Tunai', 'bank' => 'Bank', 'ewallet' => 'E-Wallet', 'investment' => 'Investasi'][$a->type] }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium tabular-nums">{{ $bal < 0 ? '−' : '' }}Rp
                            {{ number_format(abs($bal), 0, ',', '.') }}</td>
                        <td class="space-x-3 px-4 py-3 text-right text-xs">
                            @if($a->is_active)
                                <button wire:click="edit({{ $a->id }})" class="text-gray-500 hover:text-gray-900">Ubah</button>
                                @unless($a->is_default)
                                    <button wire:click="makeDefault({{ $a->id }})" class="text-indigo-600 hover:underline">Jadikan
                                        default</button>
                                    <button wire:click="archive({{ $a->id }})" wire:confirm="Arsipkan akun ini?"
                                        class="text-gray-400 hover:text-rose-600">Arsipkan</button>
                                @endunless
                            @else
                                <button wire:click="restore({{ $a->id }})"
                                    class="text-indigo-600 hover:underline">Aktifkan</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-erp.card>
</div>