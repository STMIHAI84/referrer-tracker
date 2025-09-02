<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Landing Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; border-left: 4px solid #4CAF50; }
        .info-card { border-left-color: #2196F3; }
        .text-muted { color: #666; font-size: 0.9em; }
        a { color: #2196F3; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .badge { background: #4361ee; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8em; }
        pre { background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
<a href="{{ route('admin.referrers') }}">📊 Admin Referrers</a>
<a href="{{ route('generate.links') }}" style="margin-left: 1rem;">🔗 Generate Links</a>

<h1>Pagina Landing /landing</h1>

<div class="card {{ $entry ? '' : 'info-card' }}">
    <h3>Status Tracking</h3>
    <p>{{ $message }}</p>
</div>

@if($entry)
    <div class="card">
        <h3>Detalii înregistrare:</h3>
        <p><strong>ID:</strong> <span class="badge">#{{ $entry->id }}</span></p>
        <p><strong>Sursa:</strong> {{ $entry->source }}</p>

        @if($entry->utm_source)
            <p><strong>UTM Source:</strong> {{ $entry->utm_source }}</p>
            <p><strong>UTM Medium:</strong> {{ $entry->utm_medium }}</p>
            <p><strong>UTM Campaign:</strong> {{ $entry->utm_campaign }}</p>
        @endif

        @if($entry->referral_code)
            <p><strong>Referral Code:</strong> {{ $entry->referral_code }}</p>
        @endif

        <p><strong>Landing Page:</strong> {{ $entry->landing_path }}</p>
        <p><strong>IP:</strong> {{ $entry->ip }}</p>
        <p><strong>User-Agent:</strong> {{ Str::limit($entry->user_agent, 120) }}</p>
        <p class="text-muted">Creat la: {{ $entry->created_at->format('d.m.Y H:i:s') }}</p>
    </div>
@endif

<div class="card">
    <h3>Parametri primiți:</h3>
    <pre>{{ json_encode($queryParams, JSON_PRETTY_PRINT) }}</pre>
</div>

<div class="card">
    <h3>Testează cu:</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="/landing?utm_source=facebook" style="background: #1877F2; color: white; padding: 0.5rem 1rem; border-radius: 6px;">Facebook</a>
        <a href="/landing?utm_source=instagram" style="background: #E4405F; color: white; padding: 0.5rem 1rem; border-radius: 6px;">Instagram</a>
        <a href="/landing?utm_source=whatsapp" style="background: #25D366; color: white; padding: 0.5rem 1rem; border-radius: 6px;">WhatsApp</a>
        <a href="/landing?ref=twitter" style="background: #1DA1F2; color: white; padding: 0.5rem 1rem; border-radius: 6px;">Twitter</a>
        <a href="/landing?source=organic" style="background: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 6px;">Organic</a>
        <a href="/landing" style="background: #495057; color: white; padding: 0.5rem 1rem; border-radius: 6px;">Direct</a>
    </div>
</div>
</body>
</html>
