<div class="space-y-5">
    <x-erp.card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">API Keys</h3>
                <p class="mt-1 text-sm text-slate-500">Dipakai bot atau aplikasi lain untuk mencatat dan membaca
                    transaksimu.
                    Nama key menjadi label asal transaksi.</p>
            </div>
            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                {{ $activeCount }}/{{ \App\Livewire\Settings\ApiKeys::MAX_KEYS }} aktif
            </span>
        </div>

        <form wire:submit="create" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="flex-1">
                <input wire:model="name" maxlength="20" placeholder="Nama key (mis. hermes)"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Buat
                key</button>
        </form>

        <p class="mt-4 text-xs text-slate-400">Endpoint:
            <code class="rounded bg-slate-100 px-1.5 py-0.5">{{ rtrim(config('app.url'), '/') }}/api/v1/finance</code>
        </p>

        @if($newToken)
            <div wire:key="new-token" class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4" x-data="{
                        copied: false,
                        async copy() {
                            const t = this.$refs.tok.innerText.trim();
                            try { await navigator.clipboard.writeText(t); }
                            catch (e) {
                                const r = document.createRange(); r.selectNodeContents(this.$refs.tok);
                                const s = getSelection(); s.removeAllRanges(); s.addRange(r);
                                document.execCommand('copy'); s.removeAllRanges();
                            }
                            this.copied = true; setTimeout(() => this.copied = false, 2000);
                        }
                     }">
                <p class="text-sm font-medium text-amber-800">
                    Key “{{ $newTokenName }}” berhasil dibuat. Salin sekarang, key ini tidak akan ditampilkan lagi.
                </p>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <code x-ref="tok"
                        class="block flex-1 select-all break-all rounded-lg bg-white px-3 py-2 text-xs text-slate-800">{{ $newToken }}</code>
                    <button type="button" @click="copy"
                        class="shrink-0 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                        <span x-show="!copied">Salin</span>
                        <span x-show="copied" x-cloak>Tersalin ✓</span>
                    </button>
                </div>
                <button type="button" wire:click="dismiss" class="mt-3 text-xs text-amber-800 underline">
                    Sudah saya simpan, tutup
                </button>
            </div>
        @endif
    </x-erp.card>

    <x-erp.card flush>
        <div class="overflow-x-auto rounded-xl">
            <x-erp.table>
                <thead class="bg-slate-800 text-[11px] font-medium uppercase tracking-wider text-white">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Dibuat</th>
                        <th class="px-4 py-3">Terakhir dipakai</th>
                        <th class="px-4 py-3">Kedaluwarsa</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tokens as $t)
                        @php $expired = $t->expires_at && $t->expires_at->isPast(); @endphp
                        <tr wire:key="tk-{{ $t->id }}" class="{{ $expired ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 font-medium">{{ $t->name }}
                                @if($expired)<span
                                class="ml-1 rounded bg-rose-50 px-1.5 py-0.5 text-xs text-rose-600">kedaluwarsa</span>@endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $t->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $t->last_used_at?->diffForHumans() ?? 'belum pernah' }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $t->expires_at?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs">
                                <button wire:click="revoke({{ $t->id }})"
                                    wire:confirm="Cabut key {{ $t->name }}? Aplikasi yang memakainya langsung berhenti bekerja."
                                    class="text-slate-400 hover:text-rose-600">Cabut</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada key.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-erp.table>
        </div>
    </x-erp.card>
</div>