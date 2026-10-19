{{-- resources/views/livewire/admin/journal-manager.blade.php --}}
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 text-sm">
            <option value="">Semua status</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </select>
        <button wire:click="create"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+
            Entri</button>
    </div>

    <x-erp.card class="p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Diubah</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($entries as $e)
                    <tr wire:key="je-{{ $e->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $e->title }}</p>
                            <p class="text-xs text-gray-500">/{{ $e->slug }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $e->type }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="togglePublish({{ $e->id }})"
                                class="rounded px-2 py-0.5 text-xs {{ $e->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $e->status }}</button>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $e->updated_at->diffForHumans() }}</td>
                        <td class="space-x-3 px-4 py-3 text-right text-xs">
                            <button wire:click="edit({{ $e->id }})" class="text-gray-500 hover:text-gray-900">Ubah</button>
                            <button wire:click="delete({{ $e->id }})" wire:confirm="Hapus entri ini?"
                                class="text-gray-400 hover:text-rose-600">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada entri.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $entries->links() }}</div>
    </x-erp.card>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
            wire:key="journal-form">
            <div class="my-8 w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold">{{ $editingId ? 'Ubah entri' : 'Entri baru' }}</h3>

                <form wire:submit="save" class="mt-4 space-y-3 text-sm">
                    <div class="grid grid-cols-3 gap-3">
                        <select wire:model="type" class="rounded-lg border-gray-300 text-sm">
                            <option value="exploration">Exploration</option>
                            <option value="server">Server notes</option>
                            <option value="hiking">Hiking</option>
                            <option value="ui_thoughts">UI thoughts</option>
                            <option value="learning_log">Learning log</option>
                        </select>
                        <select wire:model="status" class="rounded-lg border-gray-300 text-sm">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div>
                        <input wire:model="title" placeholder="Judul" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('title')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <input wire:model="excerpt" placeholder="Ringkasan singkat (opsional)"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('excerpt')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <input wire:model="tagsText" placeholder="Tag, pisahkan koma (server, learning)"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('tagsText')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">Gambar hero (maks 4 MB)</label>
                        <input type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm">
                        @error('image')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="image" class="text-xs text-gray-500">Mengunggah…</div>
                        @if($image)<img src="{{ $image->temporaryUrl() }}" class="mt-2 h-24 rounded" alt="">
                        @elseif($currentImage)<img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentImage) }}"
                        class="mt-2 h-24 rounded" alt="">@endif
                    </div>

                    <div>
                        <textarea wire:model="body" rows="14" placeholder="Isi (Markdown)"
                            class="w-full rounded-lg border-gray-300 font-mono text-xs"></textarea>
                        @error('body')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancel"
                            class="text-sm text-gray-500 hover:text-gray-800">Batal</button>
                        <button
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>