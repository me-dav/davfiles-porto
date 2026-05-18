<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Dashboard Dav-Files</title>
  <link rel="preconnect" href="https://api.fontshare.com" />
  <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@900&f[]=satoshi@400,500&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --ink: #0E0E0F; --graphite: #1B1B1C; --slate: #2E2E30;
      --stone: #5C5957; --ash: #BFBAB6; --sand: #F7F5F3;
    }
    body {
      background: var(--ink);
      color: var(--sand);
      font-family: 'Satoshi', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      -webkit-font-smoothing: antialiased;
    }
    .box {
      width: 100%;
      max-width: 380px;
    }
    h1 {
      font-family: 'Cabinet Grotesk', sans-serif;
      font-weight: 900;
      font-size: 2.5rem;
      text-transform: uppercase;
      letter-spacing: -.03em;
      color: var(--sand);
      margin-bottom: .25rem;
    }
    .sub {
      font-size: 11px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--stone);
      margin-bottom: 2.5rem;
    }
    .alert-error {
      background: rgba(180,40,40,.12);
      border: 1px solid rgba(180,40,40,.4);
      color: #f4a0a0;
      font-size: 12px;
      padding: .7rem 1rem;
      border-radius: 2px;
      margin-bottom: 1.25rem;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      margin-bottom: 1.1rem;
    }
    label {
      font-size: 10px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--stone);
    }
    input {
      background: var(--graphite);
      border: 1px solid var(--slate);
      color: var(--sand);
      font-size: 13px;
      font-family: 'Satoshi', sans-serif;
      padding: .7rem .9rem;
      border-radius: 2px;
      outline: none;
      transition: border-color .15s;
      width: 100%;
    }
    input:focus { border-color: var(--ash); }
    input::placeholder { color: rgba(92,89,87,.5); }
    .remember-row {
      display: flex;
      align-items: center;
      gap: .6rem;
      margin-bottom: 1.5rem;
    }
    .remember-row input[type="checkbox"] {
      width: 15px; height: 15px;
      accent-color: var(--ash);
      cursor: pointer;
    }
    .remember-row label {
      font-size: 12px;
      color: var(--stone);
      text-transform: none;
      letter-spacing: 0;
      cursor: pointer;
    }
    button[type="submit"] {
      width: 100%;
      background: transparent;
      border: 1px solid rgba(191,186,182,.4);
      color: var(--sand);
      font-family: 'Satoshi', sans-serif;
      font-size: 11px;
      letter-spacing: .15em;
      text-transform: uppercase;
      padding: .8rem;
      border-radius: 2px;
      cursor: pointer;
      transition: background .15s;
    }
    button[type="submit"]:hover { background: rgba(92,89,87,.2); }
  </style>
</head>
<body>
  <div class="box">
    <h1>Dashboard</h1>
    <p class="sub">Sign in to continue</p>

    @if ($errors->any())
      <div class="alert-error">
        @foreach ($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
      @csrf

      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          type="email"
          name="email"
          value="{{ old('email') }}"
          required
          autofocus
          autocomplete="email"
          placeholder="admin@example.com"
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          type="password"
          name="password"
          required
          autocomplete="current-password"
          placeholder="••••••••"
        />
      </div>

      <div class="remember-row">
        <input type="checkbox" id="remember" name="remember" />
        <label for="remember">Remember me</label>
      </div>

      <button type="submit">Sign In</button>
    </form>
  </div>
</body>
</html>