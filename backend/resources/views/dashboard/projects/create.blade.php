@extends('layouts.dashboard')
@section('title', 'New Project')

@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">New Project</h1>
    <p class="page-sub">Isi detail project di bawah</p>
  </div>
  <a href="{{ route('dashboard.projects.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<form method="POST" action="{{ route('dashboard.projects.store') }}" style="max-width:820px">
  @csrf

  <div class="form-grid">

    {{-- Name --}}
    <div class="form-group">
      <label for="name">Name *</label>
      <input id="name" type="text" name="name" value="{{ old('name') }}"
             required placeholder="Nama project" oninput="autoSlug(this.value)" />
    </div>

    {{-- Slug --}}
    <div class="form-group">
      <label for="slug">Slug * <span style="text-transform:none;letter-spacing:0;font-size:9px;color:var(--stone)">(auto-generate, bisa diedit)</span></label>
      <input id="slug" type="text" name="slug" value="{{ old('slug') }}" required
             placeholder="nama-project" />
    </div>

    {{-- Role --}}
    <div class="form-group">
      <label for="role">Role</label>
      <input id="role" type="text" name="role" value="{{ old('role') }}"
             placeholder="Full-Stack Developer" />
    </div>

    {{-- Year --}}
    <div class="form-group">
      <label for="year">Year *</label>
      <input id="year" type="number" name="year" value="{{ old('year', date('Y')) }}"
             required min="2000" max="2100" />
    </div>

    {{-- Short description --}}
    <div class="form-group form-full">
      <label for="short_description">Short Description</label>
      <input id="short_description" type="text" name="short_description"
             value="{{ old('short_description') }}"
             placeholder="Satu kalimat yang muncul di card" />
    </div>

    {{-- Stack --}}
    <div class="form-group form-full">
      <label for="stack">Stack <span style="text-transform:none;letter-spacing:0;font-size:9px;color:var(--stone)">(pisahkan dengan koma)</span></label>
      <input id="stack" type="text" name="stack" value="{{ old('stack') }}"
             placeholder="Next.js, Laravel, MySQL" />
    </div>

    {{-- Preview image --}}
    <div class="form-group form-full">
      <label>Preview Image</label>
      <input type="file" id="image-file" accept="image/jpeg,image/png,image/webp,image/gif"
             style="color:var(--stone); font-size:12px" />
      <div class="upload-status" id="upload-status" style="display:none">Mengupload…</div>
      <div class="upload-preview" id="upload-preview" style="display:none">
        <img id="preview-img" src="" alt="" />
        <span id="preview-url"></span>
      </div>
      {{-- Hidden input yang dikirim ke server --}}
      <input type="hidden" id="preview_image_url" name="preview_image_url"
             value="{{ old('preview_image_url') }}" />
      @if(old('preview_image_url'))
        <div class="upload-preview" style="display:flex; margin-top:.35rem">
          <img src="{{ old('preview_image_url') }}" alt="" />
          <span style="font-size:10px; color:var(--stone)">{{ old('preview_image_url') }}</span>
        </div>
      @endif
    </div>

    {{-- Live URL --}}
    <div class="form-group">
      <label for="live_url">Live URL</label>
      <input id="live_url" type="url" name="live_url" value="{{ old('live_url') }}"
             placeholder="https://example.com" />
    </div>

    {{-- GitHub URL --}}
    <div class="form-group">
      <label for="github_url">GitHub URL</label>
      <input id="github_url" type="url" name="github_url" value="{{ old('github_url') }}"
             placeholder="https://github.com/..." />
    </div>

    {{-- Body --}}
    <div class="form-group form-full">
      <label for="body">Body (HTML) <span style="text-transform:none;letter-spacing:0;font-size:9px;color:var(--stone)">— bisa upgrade ke rich editor nanti</span></label>
      <textarea id="body" name="body" rows="14"
                placeholder="<h2>Challenge</h2>&#10;<p>...</p>&#10;<h2>Approach</h2>&#10;<p>...</p>&#10;<h2>Result</h2>&#10;<p>...</p>"
                style="font-family:monospace; font-size:12px">{{ old('body') }}</textarea>
    </div>

    {{-- Featured --}}
    <div class="form-group form-full">
      <label class="toggle-wrap">
        <input type="checkbox" name="featured" value="1"
               {{ old('featured') ? 'checked' : '' }} />
        <span class="toggle-label">Tampilkan sebagai Featured di halaman utama</span>
      </label>
    </div>

  </div>{{-- /form-grid --}}

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan Project</button>
    <a href="{{ route('dashboard.projects.index') }}" class="btn btn-ghost">Batal</a>
  </div>

</form>

@endsection

@push('scripts')
<script>
  // Auto-generate slug dari nama
  function autoSlug(val) {
    document.getElementById('slug').value = val
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s]+/g, '-')
  }

  // Upload image via fetch → POST /dashboard/upload
  document.getElementById('image-file').addEventListener('change', async function () {
    const file = this.files[0]
    if (!file) return

    const status  = document.getElementById('upload-status')
    const preview = document.getElementById('upload-preview')
    const imgEl   = document.getElementById('preview-img')
    const urlEl   = document.getElementById('preview-url')
    const hiddenInput = document.getElementById('preview_image_url')

    status.style.display = 'block'
    preview.style.display = 'none'

    const fd = new FormData()
    fd.append('file', file)
    fd.append('_token', '{{ csrf_token() }}')

    try {
      const res  = await fetch('{{ route('dashboard.upload') }}', { method: 'POST', body: fd })
      const data = await res.json()

      if (data.url) {
        hiddenInput.value   = data.url
        imgEl.src           = data.url
        urlEl.textContent   = data.url
        status.style.display  = 'none'
        preview.style.display = 'flex'
      } else {
        status.textContent = 'Upload gagal.'
      }
    } catch (e) {
      status.textContent = 'Upload error: ' + e.message
    }
  })
</script>
@endpush