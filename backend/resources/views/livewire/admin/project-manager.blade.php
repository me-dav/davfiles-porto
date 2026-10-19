{{-- resources/views/livewire/admin/project-manager.blade.php --}}
<div class="space-y-4">
    <div class="flex justify-end">
        <button wire:click="create"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+
            Project</button>
    </div>

    <x-erp.card class="p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Featured</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($projects as $p)
                    <tr wire:key="pr-{{ $p->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($p->imageUrl())<img src="{{ $p->imageUrl() }}" class="h-10 w-14 rounded object-cover"
                                alt="">@endif
                                <div>
                                    <p class="font-medium">{{ $p->name }}</p>
                                    <p class="text-xs text-gray-500">/{{ $p->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->category }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->year }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleFeatured({{ $p->id }})"
                                class="rounded px-2 py-0.5 text-xs {{ $p->featured ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $p->featured ? 'ya' : 'tidak' }}</button>
                        </td>
                        <td class="space-x-3 px-4 py-3 text-right text-xs">
                            <button wire:click="edit({{ $p->id }})" class="text-gray-500 hover:text-gray-900">Ubah</button>
                            <button wire:click="delete({{ $p->id }})" wire:confirm="Hapus project ini?"
                                class="text-gray-400 hover:text-rose-600">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada project.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $projects->links() }}</div>
    </x-erp.card>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
            wire:key="project-form">
            <div class="my-8 w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold">{{ $editingId ? 'Ubah project' : 'Project baru' }}</h3>

                <form wire:submit="save" class="mt-4 space-y-3 text-sm">
                    <div>
                        <input wire:model="name" placeholder="Nama project"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <input wire:model="short_description" placeholder="Deskripsi singkat (maks 280 karakter)"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('short_description')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div><input wire:model="role" placeholder="Role" class="w-full rounded-lg border-gray-300 text-sm">
                            @error('role')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <select wire:model="category" class="rounded-lg border-gray-300 text-sm">
                            @foreach(['Web', 'IoT', 'Editing', 'Other'] as $c)
                            <option>{{ $c }}</option>@endforeach
                        </select>
                        <div><input wire:model="year" placeholder="Tahun" class="w-full rounded-lg border-gray-300 text-sm">
                            @error('year')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <input wire:model="stackText" placeholder="Stack, pisahkan koma (Next.js, Tailwind, GSAP)"
                            class="w-full rounded-lg border-gray-300 text-sm">
                        @error('stackText')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><input wire:model="live_url" placeholder="Link live (https://…)"
                                class="w-full rounded-lg border-gray-300 text-sm">
                            @error('live_url')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div><input wire:model="github_url" placeholder="Link GitHub"
                                class="w-full rounded-lg border-gray-300 text-sm">
                            @error('github_url')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">Gambar preview (maks 4 MB)</label>
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
                        <textarea wire:model="body" rows="10"
                            placeholder="Case study (Markdown): ## Challenge, ## Approach, ## Result"
                            class="w-full rounded-lg border-gray-300 font-mono text-xs"></textarea>
                    </div>

                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="featured"
                            class="rounded border-gray-300 text-indigo-600"> Tampilkan sebagai featured</label>

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