@extends('layouts.dashboard')
@section('title', 'Journal')

@section('content')

<div class="page-header">
  <div>
    <h1 class="page-title">Journal</h1>
    <p class="page-sub">{{ $entries->total() }} entr{{ $entries->total() !== 1 ? 'i' : 'y' }}</p>
  </div>
  <a href="{{ route('dashboard.journal.create') }}" class="btn btn-primary">+ New Entry</a>
</div>

@if ($entries->isEmpty())
  <div class="empty-state">
    <p>Belum ada journal entry.</p>
    <a href="{{ route('dashboard.journal.create') }}" class="btn btn-primary">Buat Entry Pertama</a>
  </div>
@else
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:38%">Title</th>
          <th style="width:15%">Type</th>
          <th style="width:12%">Status</th>
          <th style="width:15%">Published</th>
          <th style="width:20%">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($entries as $entry)
          <tr>
            <td>
              <div class="td-name">{{ $entry->title }}</div>
              <div class="td-mono">/{{ $entry->slug }}</div>
            </td>
            <td>
              <span style="font-size:11px; color:var(--stone); text-transform:uppercase; letter-spacing:.05em">
                {{ $entry->type }}
              </span>
            </td>
            <td>
              <span class="badge-status {{ $entry->status === 'published' ? 'badge-published' : 'badge-draft' }}">
                {{ $entry->status }}
              </span>
            </td>
            <td style="font-size:11px; color:var(--stone)">
              {{ $entry->published_at ? $entry->published_at->format('d M Y') : '—' }}
            </td>
            <td>
              <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap">
                <a href="{{ route('dashboard.journal.edit', $entry) }}" class="btn btn-ghost" style="padding:.3rem .6rem">Edit</a>

                <form method="POST" action="{{ route('dashboard.journal.destroy', $entry) }}"
                      onsubmit="return confirm('Hapus entry ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem">Hapus</button>
                </form>

                @if ($entry->status === 'published')
                  <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/journal/{{ $entry->slug }}"
                     target="_blank" class="btn btn-ghost" style="padding:.3rem .6rem; font-size:10px">
                    View ↗
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if ($entries->hasPages())
    <div class="pagination">
      @if ($entries->onFirstPage())
        <span>←</span>
      @else
        <a href="{{ $entries->previousPageUrl() }}">←</a>
      @endif

      @for ($i = 1; $i <= $entries->lastPage(); $i++)
        @if ($i == $entries->currentPage())
          <span class="active-page">{{ $i }}</span>
        @else
          <a href="{{ $entries->url($i) }}">{{ $i }}</a>
        @endif
      @endfor

      @if ($entries->hasMorePages())
        <a href="{{ $entries->nextPageUrl() }}">→</a>
      @else
        <span>→</span>
      @endif
    </div>
  @endif
@endif

@endsection