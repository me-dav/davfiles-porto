{{-- resources/views/livewire/finance/account-manager.blade.php --}}
<div class="space-y-4">
    @if(session('synced'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('synced') }}</div>
    @endif
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
                    @php $bal = $balances[$a->id]; @endphp
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
                            {{ number_format(abs($bal), 0, ',', '.') }}
                        </td>
                        <td class="space-x-3 px-4 py-3 text-right text-xs">
                            @if($a->is_active)
                                <button wire:click="openSync({{ $a->id }})" class="text-blue-600 hover:underline">Sync
                                    saldo</button>
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
    @if($syncAccount)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="sync-modal">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-slate-900">Sync saldo · {{ $syncAccount->name }}</h3>
                <p class="mt-1 text-sm text-slate-500">Cocokkan dengan saldo di m-banking atau aplikasinya.</p>

                <div class="mt-4 flex justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm">
                    <span class="text-slate-500">Saldo di web</span>
                    <span class="font-medium tabular-nums">Rp
                        {{ number_format($balances[$syncAccount->id], 0, ',', '.') }}</span>
                </div>

                <form wire:submit="confirmSync" class="mt-4 space-y-3">
                    <div>
                        <input wire:model.live.debounce.400ms="syncAmount" placeholder="Saldo di m-banking (mis. 90rb)"
                            autofocus class="w-full rounded-lg border-slate-300 text-sm tabular-nums">
                        @error('syncAmount')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if($preview)
                        @if($preview['diff'] === 0)
                            <p class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700">Sudah sesuai. Tidak ada yang
                                dicatat.</p>
                        @elseif($preview['diff'] < 0)
                            <p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                Selisih −Rp {{ number_format(abs($preview['diff']), 0, ',', '.') }} dicatat sebagai
                                <b>pengeluaran</b> kategori “{{ \App\Actions\Finance\SyncAccountBalance::LOST }}”.
                            </p>
                        @else
                            <p class="rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-700">
                                Selisih +Rp {{ number_format($preview['diff'], 0, ',', '.') }} dicatat sebagai
                                <b>pemasukan</b> kategori “{{ \App\Actions\Finance\SyncAccountBalance::FOUND }}”.
                            </p>
                        @endif
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeSync"
                            class="text-sm text-slate-500 hover:text-slate-800">Batal</button>
                        <button @disabled(!$preview) wire:loading.attr="disabled" wire:target="confirmSync"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>