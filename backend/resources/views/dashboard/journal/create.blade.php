@extends('layouts.dashboard')
@section('title', 'New Journal Entry')

@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">New Entry</h1>
    <p class="page-sub">Tulis sesuatu yang layak disimpan</p>
  </div>
  <a href="{{ route('dashboard.journal.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<form method="POST" action="{{ route('dashboard.journal.store') }}" style="max-width:820px">
  @csrf

  <div class="form-grid">

    {{-- Title --}}
    <div class="form-group form-full">
      <label for="title">Title *</label>
      <input id="title" type="text" name="title" value="{{ old('title') }}"
             required placeholder="Judul entry" oninput="autoSlug(this.value)" />
    </div>

    {{-- Slug --}}
    <div class="form-group form-full">
      <label for="slug">Slug *</label>
      <input id="slug" type="text" name="slug" value="{{ old('slug') }}" required
             placeholder="judul-entry" />
    </div>

    {{-- Type --}}
    <div class="form-group">
      <label for="type">Type *</label>
      <select id="type" name="type" required>
        @foreach (['exploration','server-notes','hiking','ui-thoughts','learning-log'] as $t)
          <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
      </select>
    </div>

    {{-- Status --}}
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="draft"     {{ old('status','draft') === 'draft'     ? 'selected' : '' }}>Draft</option>
        <option value="published" {{ old('status') === 'published'         ? 'selected' : '' }}>Published</option>
      </select>
      <p class="form-hint">Published → published_at otomatis di-set ke sekarang</p>
    </div>

    {{-- Tags --}}
    <div class="form-group form-full">
      <label for="tags">Tags <span style="text-transform:none;letter-spacing:0;font-size:9px;color:var(--stone)">(pisahkan dengan koma)</span></label>
      <input id="tags" type="text" name="tags" value="{{ old('tags') }}"
             placeholder="server, proxmox, homelab" />
    </div>

    {{-- Hero image --}}
    <div class="form-group form-full">
      <label>Hero Image</label>
      <input type="file" id="image-file" accept="image/jpeg,image/png,image/webp,image/gif"
             style="color:var(--stone); font-size:12px" />
      <div class="upload-status" id="upload-status" style="display:none">Mengupload…</div>
      <div class="upload-preview" id="upload-preview" style="display:none">
        <img id="preview-img" src="" alt="" />
        <span id="preview-url"></span>
      </div>
      <input type="hidden" id="hero_image_url" name="hero_image_url"
             value="{{ old('hero_image_url') }}" />
    </div>

    {{-- Body --}}
    <div class="form-group form-full">
      <label for="body">Body (HTML)</label>
      <textarea id="body" name="body" rows="18"
                placeholder="<p>Mulai nulis...</p>"
                style="font-family:monospace; font-size:12px">{{ old('body') }}</textarea>
    </div>

  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan Entry</button>
    <a href="{{ route('dashboard.journal.index') }}" class="btn btn-ghost">Batal</a>
  </div>

</form>

@endsection

@push('scripts')
<script>
  function autoSlug(val) {
    document.getElementById('slug').value = val
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s]+/g, '-')
  }

  document.getElementById('image-file').addEventListener('change', async function () {
    const file = this.files[0]
    if (!file) return
    const status  = document.getElementById('upload-status')
    const preview = document.getElementById('upload-preview')
    const imgEl   = document.getElementById('preview-img')
    const urlEl   = document.getElementById('preview-url')
    const hidden  = document.getElementById('hero_image_url')

    status.style.display = 'block'

    const fd = new FormData()
    fd.append('file', file)
    fd.append('_token', '{{ csrf_token() }}')

    try {
      const res  = await fetch('{{ route('dashboard.upload') }}', { method: 'POST', body: fd })
      const data = await res.json()
      if (data.url) {
        hidden.value          = data.url
        imgEl.src             = data.url
        urlEl.textContent     = data.url
        status.style.display  = 'none'
        preview.style.display = 'flex'
      }
    } catch (e) {
      status.textContent = 'Upload error: ' + e.message
    }
  })
</script>
@endpush