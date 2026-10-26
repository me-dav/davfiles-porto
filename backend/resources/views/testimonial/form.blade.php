<x-layouts.testimonial :title="'Testimoni · ' . $project->name">
    <p class="text-[11px] font-medium uppercase tracking-[0.25em] text-slate-400">Testimoni</p>
    <h1 class="f-display mt-3 text-5xl uppercase leading-[0.95] sm:text-6xl">{{ $project->name }}</h1>
    <p class="f-body mt-6 max-w-lg text-lg leading-relaxed text-slate-600">
        Terima kasih sudah bekerja sama. Ceritakan pengalamanmu secara singkat dan jujur, karena itu sangat membantu.
    </p>
    <hr class="mt-10 border-slate-200">

    <form method="POST" action="{{ route('testimonial.store', $token) }}" class="mt-10 space-y-8"
        onsubmit="const b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent='Mengirim…';">
        @csrf
        <input type="hidden" name="k" value="{{ $formKey }}">

        {{-- kolom jebakan bot --}}
        <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        @php
            $label = 'text-[11px] font-medium uppercase tracking-[0.2em] text-slate-500';
            $field = 'mt-2 block w-full rounded-lg border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-slate-900 focus:ring-slate-900';
        @endphp

        <div class="grid gap-8 sm:grid-cols-2">
            <div>
                <label for="name" class="{{ $label }}">Nama</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="name"
                    class="{{ $field }}">
                @error('name')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="150"
                    autocomplete="email" class="{{ $field }}">
                <p class="mt-2 text-xs text-slate-400">Tidak ditampilkan penuh. Hanya sebagian yang disamarkan.</p>
                @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <fieldset>
            <legend class="{{ $label }}">Penilaian</legend>
            <div class="mt-3 grid grid-cols-5 gap-2">
                @for($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" required @checked((int) old('rating') === $i)>
                        <span
                            class="f-display flex h-14 items-center justify-center rounded-lg border border-slate-300 text-2xl text-slate-400 transition
                                         hover:border-slate-500 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900 peer-focus-visible:ring-offset-2">{{ $i }}</span>
                    </label>
                @endfor
            </div>
            <div class="mt-2 flex justify-between text-[11px] text-slate-400"><span>Kurang puas</span><span>Sangat
                    puas</span></div>
            @error('rating')
            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        </fieldset>

        <div>
            <label for="body" class="{{ $label }}">Testimoni</label>
            <textarea id="body" name="body" rows="7" required minlength="20" maxlength="1000"
                oninput="document.getElementById('cnt').textContent=this.value.length"
                placeholder="Bagaimana pengalaman bekerja sama, dan apa hasilnya?"
                class="f-body {{ $field }} text-base leading-relaxed">{{ old('body') }}</textarea>
            <div class="mt-2 flex items-start justify-between gap-4">
                <div>@error('body')
                <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <p class="shrink-0 text-xs tabular-nums text-slate-400"><span
                        id="cnt">{{ mb_strlen(old('body', '')) }}</span>/1000</p>
            </div>
        </div>

        <div>
            <label class="flex items-start gap-3 text-sm leading-relaxed text-slate-600">
                <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                    class="mt-1 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <span>Saya setuju testimoni ini ditampilkan di situs publik, dengan email disamarkan.</span>
            </label>
            @error('consent')
            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <button type="submit"
                class="f-display w-full rounded-lg bg-slate-900 px-6 py-4 text-lg uppercase tracking-wide text-white transition hover:bg-black disabled:opacity-60">
                Kirim testimoni
            </button>
            <p class="mt-3 text-center text-xs text-slate-400">Testimoni ditinjau dulu sebelum ditampilkan.</p>
        </div>
    </form>
</x-layouts.testimonial>