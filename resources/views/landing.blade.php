<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Landing Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="page">
<header class="container header">
    <nav class="header-actions">
        <a href="{{ route('admin.referrers') }}" class="btn">📊 Admin</a>
        <a href="{{ route('generate.links') }}" class="btn btn-secondary">🔗 Generate Links</a>
    </nav>
</header>

<main class="container">
    <h1 class="page-title">Pagina Landing</h1>

    <section class="card {{ $entry ? 'card-success' : 'card-info' }}">
        <h3 class="card-title">Status Tracking</h3>
        <p>{{ $message }}</p>
    </section>

    @if($entry)
        <section class="card">
            <h3 class="card-title">Detalii înregistrare</h3>
            <div class="details-grid">
                <div><strong>ID</strong> <span class="badge">#{{ $entry->id }}</span></div>
                <div><strong>Sursa</strong> <span class="badge badge-source">{{ $entry->source }}</span></div>

                @if($entry->utm_source)
                    <div><strong>UTM Source</strong> {{ $entry->utm_source }}</div>
                    <div><strong>UTM Medium</strong> {{ $entry->utm_medium }}</div>
                    <div><strong>UTM Campaign</strong> {{ $entry->utm_campaign }}</div>
                @endif

                @if($entry->referral_code)
                    <div><strong>Referral Code</strong> {{ $entry->referral_code }}</div>
                @endif

                <div><strong>Landing Page</strong> {{ $entry->landing_path }}</div>
                <div><strong>IP</strong> {{ $entry->ip }}</div>
                <div class="span-2">
                    <strong>User-Agent</strong>
                    <div class="mono">{{ \Illuminate\Support\Str::limit($entry->user_agent, 200) }}</div>
                </div>
                <div class="text-muted span-2">Creat la: {{ $entry->created_at->format('d.m.Y H:i:s') }}</div>
            </div>
        </section>
    @endif

    <section class="card">
        <h3 class="card-title">Parametri primiți</h3>
        <pre class="pre">{{ json_encode($queryParams, JSON_PRETTY_PRINT) }}</pre>
    </section>

    <section class="card">
        <h3 class="card-title">Teste rapide</h3>
        <div class="btn-row">
            <a href="/landing?utm_source=facebook" class="btn btn-fb">Facebook</a>
            <a href="/landing?utm_source=instagram" class="btn btn-ig">Instagram</a>
            <a href="/landing?utm_source=whatsapp" class="btn btn-wa">WhatsApp</a>
            <a href="/landing?ref=twitter" class="btn btn-tw">Twitter</a>
            <a href="/landing?source=organic" class="btn btn-dark">Organic</a>
            <a href="/landing" class="btn btn-outline">Direct</a>
        </div>
    </section>

    <section class="card card-muted">
        <h3 class="card-title">Debug client (doar orientativ)</h3>
        <div class="details-grid">
            <div class="span-2"><strong>document.referrer</strong> <span class="mono" id="js-referrer">—</span></div>
            <div class="span-2"><strong>navigator.userAgent</strong> <span class="mono" id="js-ua">—</span></div>
            <div><strong>Referrer Policy</strong> <span class="mono" id="js-rp">—</span></div>
        </div>
    </section>
</main>

<script>
    document.getElementById('js-referrer').textContent = document.referrer || '— (gol / blocat)';
    document.getElementById('js-ua').textContent = navigator.userAgent || '—';
    try {
        document.getElementById('js-rp').textContent = getComputedStyle(document.querySelector('a'))?.referrerPolicy || 'implicit browser';
    } catch { document.getElementById('js-rp').textContent = 'n/a'; }
</script>
</body>
</html>
