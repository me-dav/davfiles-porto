{{-- resources/views/livewire/finance/quick-add.blade.php --}}
<x-erp.card>
    <form wire:submit="save" class="flex flex-col gap-3 lg:flex-row lg:items-start">
        <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm">
            <button type="button" wire:click="$set('type','expense')"
                class="rounded-md px-3 py-1.5 {{ $type === 'expense' ? 'bg-rose-50 text-rose-700' : 'text-gray-500' }}">Keluar</button>
            <button type="button" wire:click="$set('type','income')"
                class="rounded-md px-3 py-1.5 {{ $type === 'income' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-500' }}">Masuk</button>
        </div>

        <div>
            <input wire:model="amount" inputmode="text" placeholder="25rb"
                class="w-full rounded-lg border-gray-300 text-sm tabular-nums lg:w-36" autofocus>
            @error('amount')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <select wire:model="categoryId" class="w-full rounded-lg border-gray-300 text-sm lg:w-44">
                <option value="">Kategori…</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            @error('categoryId')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <input type="date" wire:model="date" max="{{ today()->toDateString() }}"
                class="w-full rounded-lg border-gray-300 text-sm lg:w-40">
            @error('date')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <input wire:model="note" placeholder="Catatan singkat…"
            class="w-full flex-1 rounded-lg border-gray-300 text-sm">

        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Simpan
        </button>
    </form>
</x-erp.card>