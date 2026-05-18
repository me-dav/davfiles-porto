@extends('layouts.dashboard')
@section('title', 'Edit Entry')

@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">Edit Entry</h1>
    <p class="page-sub">{{ $entry->title }}</p>
  </div>
  <a href="{{ route('dashboard.journal.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<form method="POST" action="{{ route('dashboard.journal.update', $entry) }}" style="max-width:820px">
  @csrf
  @method('PUT')

  <div class="form-grid">

    <div class="form-group form-full">
      <label for="title">Title *</label>
      <input id="title" type="text" name="title"
             value="{{ old('title', $entry->title) }}" required />
    </div>

    <div class="form-group form-full">
      <label for="slug">Slug *</label>
      <input id="slug" type="text" name="slug"
             value="{{ old('slug', $entry->slug) }}" required />
    </div>

    <div class="form-group">
      <label for="type">Type *</label>
      <select id="type" name="type" required>
        @foreach (['exploration','server-notes','hiking','ui-thoughts','learning-log'] as $t)
          <option value="{{ $t }}" {{ old('type', $entry->type) === $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="draft"     {{ old('status', $entry->status) === 'draft'     ? 'selected' : '' }}>Draft</option>
        <option value="published" {{ old('status', $entry->status) === 'published' ? 'selected' : '' }}>Published</option>
      </select>
    </div>

    <div class="form-group form-full">
      <label for="tags">Tags</label>
      <input id="tags" type="text" name="tags"
             value="{{ old('tags', is_array($entry->tags) ? implode(', ', $entry->tags) : $entry->tags) }}"
             placeholder="server, proxmox" />
    </div>

    {{-- Hero image --}}
    <div class="form-group form-full">
      <label>Hero Image</label>
      @if ($entry->hero_image_url)
        <div class="upload-preview" id="upload-preview" style="display:flex">
          <img id="preview-img" src="{{ $entry->hero_image_url }}" alt="" />
          <span id="preview-url">{{ $entry->hero_image_url }}</span>
        </div>
      @else
        <div class="upload-preview" id="upload-preview" style="display:none">
          <img id="preview-img" src="" alt="" />
          <span id="preview-url"></span>
        </div>
      @endif
      <input type="file" id="image-file" accept="image/jpeg,image/png,image/webp,image/gif"
             style="color:var(--stone); font-size:12px; margin-top:.5rem" />
      <div class="upload-status" id="upload-status" style="display:none">Mengupload…</div>
      <input type="hidden" id="hero_image_url" name="hero_image_url"
             value="{{ old('hero_image_url', $entry->hero_image_url) }}" />
    </div>

    <div class="form-group form-full">
      <label for="body">Body (HTML)</label>
      <textarea id="body" name="body" rows="18"
                style="font-family:monospace; font-size:12px">{{ old('body', $entry->body) }}</textarea>
    </div>

  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('dashboard.journal.index') }}" class="btn btn-ghost">Batal</a>
  </div>

</form>

@endsection

@push('scripts')
<script>
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