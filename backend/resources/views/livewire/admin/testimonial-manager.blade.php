{{-- resources/views/livewire/admin/testimonial-manager.blade.php --}}
@php
    $tab = fn($on) => $on ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600';
    $labels = ['pending' => 'Draft', 'active' => 'Aktif', 'rejected' => 'Ditolak'];
@endphp
<div class="space-y-4">
    <x-erp.card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-5 text-[11px] font-medium uppercase tracking-wider">
                @foreach($labels as $key => $label)
                    <button wire:click="$set('status','{{ $key }}')" class="border-b-2 pb-1 {{ $tab($status === $key) }}">
                        {{ $label }} <span class="ml-0.5 text-slate-400">{{ $counts[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
            <select wire:model.live="projectId" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua project</option>
                @foreach($projects as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
            </select>
        </div>
    </x-erp.card>

    @forelse($rows as $r)
        <x-erp.card wire:key="ts-{{ $r->id }}">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-medium text-slate-900">{{ $r->name }}</p>
                    <p class="text-xs text-slate-500">{{ $r->email }}</p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ $r->project?->name }} · {{ $r->created_at->translatedFormat('d M Y, H:i') }}
                        @unless($r->consent) · <span class="text-rose-500">tanpa persetujuan</span>@endunless
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold tabular-nums text-slate-900">{{ $r->rating }}<span
                            class="text-sm font-normal text-slate-400">/5</span></span>
                    <span class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="h-1.5 w-4 rounded-full {{ $i <= $r->rating ? 'bg-blue-600' : 'bg-slate-200' }}"></span>
                        @endfor
                    </span>
                </div>
            </div>

            <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $r->body }}</p>

            <div class="mt-5 flex flex-wrap items-center gap-3 text-xs">
                @if($r->status !== 'active')
                    <button wire:click="setStatus({{ $r->id }}, 'active')"
                        class="rounded-lg bg-emerald-500 px-3.5 py-1.5 font-medium text-white hover:bg-emerald-600">Aktifkan</button>
                @endif
                @if($r->status === 'pending')
                    <button wire:click="setStatus({{ $r->id }}, 'rejected')"
                        class="rounded-lg border border-slate-200 px-3.5 py-1.5 font-medium text-slate-600 hover:bg-slate-50">Tolak</button>
                @endif
                @if($r->status !== 'pending')
                    <button wire:click="setStatus({{ $r->id }}, 'pending')"
                        class="text-slate-500 hover:text-slate-800">Kembalikan ke draft</button>
                @endif
                <button wire:click="delete({{ $r->id }})"
                    wire:confirm="Hapus testimoni ini permanen? Email yang sama bisa mengirim ulang."
                    class="ml-auto text-slate-400 hover:text-rose-600">Hapus</button>
            </div>
        </x-erp.card>
    @empty
        <x-erp.card>
            <p class="py-6 text-center text-sm text-slate-400">Tidak ada testimoni di kategori ini.</p>
        </x-erp.card>
    @endforelse

    {{ $rows->links() }}
</div>