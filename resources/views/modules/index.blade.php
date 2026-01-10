<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Module Manager</title>
  <style>
    body{font-family:-apple-system,system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:20px;color:#222}
    table{border-collapse:collapse;width:100%;margin-top:10px}
    th,td{border:1px solid #ddd;padding:8px}
    th{background:#f5f5f5;text-align:left}
    .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    input[type=text]{padding:8px;border:1px solid #ccc;border-radius:6px;width:320px}
    button{padding:8px 12px;border:1px solid #ccc;border-radius:6px;background:#fafafa;cursor:pointer}
    .muted{color:#687076}
    .ok{color:#0a7f3f}
    .bad{color:#9f1d1d}
  </style>
</head>
<body>
  <h1>Module Manager</h1>
  @if(session('status'))
    <p class="ok">{{ session('status') }}</p>
  @endif

  <form class="row" method="post" action="{{ url('/admin/modules/install') }}">
    @csrf
    <input type="text" name="git" placeholder="Git URL (https://...)" required />
    <input type="text" name="name" placeholder="Optional name vendor/package" />
    <button type="submit">Install</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Path</th>
        <th>Providers</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @foreach($mods as $m)
      <tr>
        <td>{{ $m['name'] }}</td>
        <td>{{ $m['enabled'] ? 'enabled' : 'disabled' }}</td>
        <td class="muted">{{ $m['path'] }}</td>
        <td class="muted">{!! nl2br(e(implode("\n", $m['providers']))) !!}</td>
        <td>
          <form method="post" action="{{ url('/admin/modules/'.($m['enabled']?'disable':'enable')) }}" style="display:inline-block">
            @csrf
            <input type="hidden" name="name" value="{{ $m['name'] }}" />
            <button type="submit">{{ $m['enabled']?'Disable':'Enable' }}</button>
          </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</body>
</html>
