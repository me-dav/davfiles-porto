@extends('layouts.dashboard')
@section('title', 'Projects')

@section('content')

<button class="hamburger-btn" onclick="toggleSidebar()">
  ☰
</button>

<div class="page-header">
  <div>
    <h1 class="page-title">Projects</h1>
    <p class="page-sub">{{ $projects->total() }} project</p>
  </div>
  <a href="{{ route('dashboard.projects.create') }}" class="btn btn-primary">+ New Project</a>
</div>

@if ($projects->isEmpty())
  <div class="empty-state">
    <p>Belum ada project.</p>
    <a href="{{ route('dashboard.projects.create') }}" class="btn btn-primary">Buat Project Pertama</a>
  </div>
@else
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:35%">Name</th>
          <th style="width:12%">Year</th>
          <th style="width:10%">Featured</th>
          <th style="width:25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($projects as $project)
          <tr>
            <td>
              <div class="td-name">{{ $project->name }}</div>
              <div class="td-mono">/{{ $project->slug }}</div>
            </td>
            <td>{{ $project->year }}</td>
            
            <td>
              @if ($project->featured)
                <span class="badge-status badge-featured">Yes</span>
              @else
                <span style="color:var(--stone); font-size:12px">—</span>
              @endif
            </td>
            <td>
              <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap">
                <a href="{{ route('dashboard.projects.edit', $project) }}" class="btn btn-ghost" style="padding:.3rem .6rem">Edit</a>

                {{-- Delete --}}
                <form method="POST" action="{{ route('dashboard.projects.destroy', $project) }}"
                      onsubmit="return confirm('Hapus project "{{ addslashes($project->name) }}"?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem">Hapus</button>
                </form>

                {{-- View public (opens Next.js) --}}
                <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/work/{{ $project->slug }}"
                   target="_blank"
                   class="btn btn-ghost"
                   style="padding:.3rem .6rem; font-size:10px">
                  View ↗
                </a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if ($projects->hasPages())
    <div class="pagination">
      {{-- Prev --}}
      @if ($projects->onFirstPage())
        <span>←</span>
      @else
        <a href="{{ $projects->previousPageUrl() }}">←</a>
      @endif

      {{-- Pages --}}
      @for ($i = 1; $i <= $projects->lastPage(); $i++)
        @if ($i == $projects->currentPage())
          <span class="active-page">{{ $i }}</span>
        @else
          <a href="{{ $projects->url($i) }}">{{ $i }}</a>
        @endif
      @endfor

      {{-- Next --}}
      @if ($projects->hasMorePages())
        <a href="{{ $projects->nextPageUrl() }}">→</a>
      @else
        <span>→</span>
      @endif
    </div>
  @endif
@endif

@endsection