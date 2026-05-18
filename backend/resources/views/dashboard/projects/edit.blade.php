@extends('layouts.dashboard')
@section('title', 'Edit Project')

@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">Edit Project</h1>
    <p class="page-sub">{{ $project->name }}</p>
  </div>
  <a href="{{ route('dashboard.projects.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<form method="POST" action="{{ route('dashboard.projects.update', $project) }}" style="max-width:820px">
  @csrf
  @method('PUT')

  <div class="form-grid">

    <div class="form-group">
      <label for="name">Name *</label>
      <input id="name" type="text" name="name"
             value="{{ old('name', $project->name) }}" required />
    </div>

    <div class="form-group">
      <label for="slug">Slug *</label>
      <input id="slug" type="text" name="slug"
             value="{{ old('slug', $project->slug) }}" required />
    </div>

    <div class="form-group">
      <label for="role">Role</label>
      <input id="role" type="text" name="role"
             value="{{ old('role', $project->role) }}" />
    </div>

    <div class="form-group">
      <label for="year">Year *</label>
      <input id="year" type="number" name="year"
             value="{{ old('year', $project->year) }}" required min="2000" max="2100" />
    </div>

    <div class="form-group form-full">
      <label for="short_description">Short Description</label>
      <input id="short_description" type="text" name="short_description"
             value="{{ old('short_description', $project->short_description) }}" />
    </div>

    <div class="form-group form-full">
      <label for="stack">Stack <span style="text-transform:none;letter-spacing:0;font-size:9px;color:var(--stone)">(pisahkan dengan koma)</span></label>
      <input id="stack" type="text" name="stack"
             value="{{ old('stack', is_array($project->stack) ? implode(', ', $project->stack) : $project->stack) }}" />
    </div>

    {{-- Preview image --}}
    <div class="form-group form-full">
      <label>Preview Image</label>
      @if ($project->preview_image_url)
        <div class="upload-preview" id="upload-preview" style="display:flex">
          <img id="preview-img" src="{{ $project->preview_image_url }}" alt="" />
          <span id="preview-url">{{ $project->preview_image_url }}</span>
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
      <input type="hidden" id="preview_image_url" name="preview_image_url"
             value="{{ old('preview_image_url', $project->preview_image_url) }}" />
    </div>

    <div class="form-group">
      <label for="live_url">Live URL</label>
      <input id="live_url" type="url" name="live_url"
             value="{{ old('live_url', $project->live_url) }}" />
    </div>

    <div class="form-group">
      <label for="github_url">GitHub URL</label>
      <input id="github_url" type="url" name="github_url"
             value="{{ old('github_url', $project->github_url) }}" />
    </div>

    <div class="form-group form-full">
      <label for="body">Body (HTML)</label>
      <textarea id="body" name="body" rows="14"
                style="font-family:monospace; font-size:12px">{{ old('body', $project->body) }}</textarea>
    </div>

    <div class="form-group form-full">
      <label class="toggle-wrap">
        <input type="checkbox" name="featured" value="1"
               {{ old('featured', $project->featured) ? 'checked' : '' }} />
        <span class="toggle-label">Tampilkan sebagai Featured di halaman utama</span>
      </label>
    </div>

  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('dashboard.projects.index') }}" class="btn btn-ghost">Batal</a>
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
    const hiddenInput = document.getElementById('preview_image_url')

    status.style.display = 'block'

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
      }
    } catch (e) {
      status.textContent = 'Upload error: ' + e.message
    }
  })
</script>
@endpush