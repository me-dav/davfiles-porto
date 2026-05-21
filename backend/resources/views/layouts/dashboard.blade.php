<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Dashboard') — David</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
  

  {{-- Google Fonts fallback (Satoshi & Cabinet Grotesk via CDN jika tidak self-host) --}}
  <link rel="preconnect" href="https://api.fontshare.com" />
  <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@700,800,900&f[]=satoshi@400,500&display=swap"
    rel="stylesheet" />

  <style>
    /* ── Reset ── */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      font-size: 16px;
      -webkit-font-smoothing: antialiased;
    }

    body {
      background: #0E0E0F;
      color: #F7F5F3;
      font-family: 'Satoshi', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
    }

    /* ── Palette ── */
    :root {
      --ink: #0E0E0F;
      --graphite: #1B1B1C;
      --slate: #2E2E30;
      --stone: #5C5957;
      --ash: #BFBAB6;
      --sand: #F7F5F3;
    }

    /* ── Sidebar ── */
    .sidebar {
      width: 220px;
      flex-shrink: 0;
      background: var(--graphite);
      border-right: 1px solid var(--slate);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 2rem 1.5rem;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
    }

    .sidebar-logo {
      font-family: 'Cabinet Grotesk', sans-serif;
      font-weight: 900;
      font-size: 11px;
      letter-spacing: .2em;
      text-transform: uppercase;
      color: var(--sand);
      text-decoration: none;
      display: block;
      margin-bottom: 2.5rem;
    }

    .sidebar-logo:hover {
      color: var(--ash);
    }

    .sidebar-section {
      font-size: 9px;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: rgba(92, 89, 87, .6);
      margin-bottom: .75rem;
    }

    .sidebar-nav {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .sidebar-nav a {
      font-size: 12px;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--stone);
      text-decoration: none;
      padding: .5rem .75rem;
      border-radius: 4px;
      transition: background .15s, color .15s;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
      background: var(--slate);
      color: var(--sand);
    }

    .sidebar-footer {
      font-size: 10px;
      color: rgba(92, 89, 87, .5);
    }

    .sidebar-footer form {
      margin-top: .5rem;
    }

    .btn-logout {
      background: none;
      border: none;
      color: var(--stone);
      font-size: 10px;
      letter-spacing: .1em;
      text-transform: uppercase;
      cursor: pointer;
      padding: 0;
      transition: color .15s;
    }

    .btn-logout:hover {
      color: var(--sand);
    }

    /* ── Main ── */
    .main {
      margin-left: 220px;
      flex: 1;
      padding: 2.5rem 3rem;
      max-width: 100%;
    }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--slate);
    }

    .page-title {
      font-family: 'Cabinet Grotesk', sans-serif;
      font-weight: 900;
      font-size: 2rem;
      text-transform: uppercase;
      letter-spacing: -.02em;
      color: var(--sand);
    }

    .page-sub {
      font-size: 11px;
      color: var(--stone);
      margin-top: .25rem;
      letter-spacing: .05em;
    }

    /* ── Alert ── */
    .alert {
      padding: .75rem 1rem;
      border-radius: 4px;
      font-size: 12px;
      margin-bottom: 1.5rem;
      border: 1px solid;
    }

    .alert-success {
      background: rgba(15, 110, 86, .15);
      border-color: rgba(15, 110, 86, .4);
      color: #5dcaa5;
    }

    .alert-error {
      background: rgba(180, 40, 40, .12);
      border-color: rgba(180, 40, 40, .4);
      color: #f4a0a0;
    }

    /* ── Button ── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: 11px;
      letter-spacing: .12em;
      text-transform: uppercase;
      text-decoration: none;
      padding: .6rem 1.5rem;
      border-radius: 2px;
      border: 1px solid;
      cursor: pointer;
      transition: background .15s, color .15s;
      font-family: 'Satoshi', sans-serif;
    }

    .btn-primary {
      border-color: rgba(191, 186, 182, .5);
      color: var(--sand);
      background: transparent;
    }

    .btn-primary:hover {
      background: rgba(92, 89, 87, .2);
    }

    .btn-danger {
      border-color: rgba(180, 40, 40, .4);
      color: #f4a0a0;
      background: transparent;
    }

    .btn-danger:hover {
      background: rgba(180, 40, 40, .15);
    }

    .btn-ghost {
      border-color: transparent;
      color: var(--stone);
      background: transparent;
    }

    .btn-ghost:hover {
      color: var(--sand);
    }

    .btn:disabled {
      opacity: .4;
      cursor: not-allowed;
    }

    /* ── Table ── */
    .table-wrap {
      border: 1px solid var(--slate);
      border-radius: 4px;
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: var(--graphite);
    }

    th {
      font-size: 9px;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: var(--stone);
      padding: .75rem 1rem;
      text-align: left;
      font-weight: 500;
    }

    td {
      font-size: 13px;
      color: var(--ash);
      padding: .875rem 1rem;
      border-top: 1px solid var(--slate);
      vertical-align: middle;
    }

    tr:hover td {
      background: rgba(46, 46, 48, .4);
    }

    .td-name {
      color: var(--sand);
      font-weight: 500;
    }

    .td-mono {
      font-family: monospace;
      font-size: 11px;
      color: var(--stone);
    }

    .badge-status {
      font-size: 9px;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 2px;
      border: 1px solid;
    }

    .badge-published {
      border-color: rgba(93, 202, 165, .4);
      color: #5dcaa5;
    }

    .badge-draft {
      border-color: rgba(92, 89, 87, .4);
      color: var(--stone);
    }

    .badge-featured {
      border-color: rgba(191, 186, 182, .3);
      color: var(--ash);
    }

    /* ── Form ── */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    .form-full {
      grid-column: 1 / -1;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }

    label {
      font-size: 10px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--stone);
    }

    input[type="text"],
    input[type="url"],
    input[type="number"],
    input[type="email"],
    input[type="password"],
    select,
    textarea {
      background: var(--graphite);
      border: 1px solid var(--slate);
      color: var(--sand);
      font-size: 13px;
      font-family: 'Satoshi', sans-serif;
      padding: .65rem .875rem;
      border-radius: 2px;
      outline: none;
      transition: border-color .15s;
      width: 100%;
    }

    input:focus,
    select:focus,
    textarea:focus {
      border-color: var(--ash);
    }

    input::placeholder,
    textarea::placeholder {
      color: rgba(92, 89, 87, .6);
    }

    textarea {
      resize: vertical;
    }

    .form-hint {
      font-size: 10px;
      color: rgba(92, 89, 87, .7);
      margin-top: 2px;
    }

    .form-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--slate);
      margin-top: .5rem;
    }

    /* ── Upload preview ── */
    .upload-preview {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin-top: .5rem;
    }

    .upload-preview img {
      width: 60px;
      height: 40px;
      object-fit: cover;
      border: 1px solid var(--slate);
    }

    .upload-preview span {
      font-size: 10px;
      color: var(--stone);
      word-break: break-all;
    }

    .upload-status {
      font-size: 11px;
      color: var(--ash);
      margin-top: .35rem;
    }

    /* ── Checkbox toggle ── */
    .toggle-wrap {
      display: flex;
      align-items: center;
      gap: .75rem;
      cursor: pointer;
    }

    .toggle-wrap input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: var(--ash);
      cursor: pointer;
    }

    .toggle-label {
      font-size: 13px;
      color: var(--ash);
      text-transform: none;
      letter-spacing: 0;
    }

    /* ── Pagination ── */
    .pagination {
      display: flex;
      gap: .35rem;
      margin-top: 1.5rem;
      align-items: center;
    }

    .pagination a,
    .pagination span {
      font-size: 11px;
      padding: .35rem .65rem;
      border: 1px solid var(--slate);
      color: var(--stone);
      text-decoration: none;
      border-radius: 2px;
      transition: background .15s, color .15s;
    }

    .pagination a:hover {
      background: var(--slate);
      color: var(--sand);
    }

    .pagination .active-page {
      background: var(--slate);
      color: var(--sand);
      border-color: var(--ash);
    }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      border: 1px dashed var(--slate);
      border-radius: 4px;
    }

    .empty-state p {
      font-size: 13px;
      color: var(--stone);
      margin-bottom: 1rem;
    }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar {
      width: 3px;
    }

    ::-webkit-scrollbar-track {
      background: var(--ink);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--slate);
      border-radius: 2px;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        z-index: 100;
        transition: transform .3s;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main {
        margin-left: 0;
        padding: 1.25rem 1rem;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }
    }

    /* ── Hamburger ── */
    .hamburger-btn {
      display: none;
      width: 42px;
      height: 42px;
      border: 1px solid var(--slate);
      background: var(--graphite);
      color: var(--sand);
      border-radius: 4px;
      cursor: pointer;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      margin-bottom: 1rem;
    }

    /* overlay */
    .sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .45);
      opacity: 0;
      visibility: hidden;
      transition: .3s;
      z-index: 90;
    }

    .sidebar-overlay.show {
      opacity: 1;
      visibility: visible;
    }

    @media (max-width: 768px) {
      .hamburger-btn {
        display: flex;
      }

      .sidebar {
        width: 260px;
        transform: translateX(-100%);
        z-index: 100;
        transition: transform .3s ease;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main {
        margin-left: 0;
        padding: 1.25rem 1rem;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  {{-- ── Sidebar ── --}}
  <aside class="sidebar" id="sidebar">
    <div>
      <a href="{{ route('dashboard.projects.index') }}" class="sidebar-logo">← David</a>

      <p class="sidebar-section">Content</p>
      <nav class="sidebar-nav">
        <a href="{{ route('dashboard.projects.index') }}"
          class="{{ request()->routeIs('dashboard.projects.*') ? 'active' : '' }}">
          Projects
        </a>
        <a href="{{ route('dashboard.journal.index') }}"
          class="{{ request()->routeIs('dashboard.journal.*') ? 'active' : '' }}">
          Journal
        </a>
      </nav>
    </div>

    <div class="sidebar-footer">
      <p>{{ Auth::user()->name }}</p>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Logout →</button>
      </form>
    </div>
  </aside>

  {{-- ── Main ── --}}
  <main class="main">

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-error">
        @foreach ($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    @yield('content')
  </main>

  @stack('scripts')
  <script>
    const sidebar = document.getElementById('sidebar')
    const overlay = document.getElementById('sidebarOverlay')

    function toggleSidebar() {
      sidebar.classList.toggle('open')
      overlay.classList.toggle('show')
    }

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open')
      overlay.classList.remove('show')
    })
  </script>
</body>

</html>