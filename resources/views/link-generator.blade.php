<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Generator Link-uri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="page">
<header class="container header">
    <nav class="header-actions">
        <a href="{{ route('landing') }}" class="btn">← Înapoi la Landing</a>
        <a href="{{ route('admin.referrers') }}" class="btn btn-secondary">📊 Admin</a>
    </nav>
</header>

<main class="container">
    <h1 class="page-title">Generator Link-uri de Tracking</h1>

    <section class="card">
        <h3 class="card-title">Link-uri pregenerate</h3>

        @forelse($links as $platform => $url)
            <div class="link-row">
                <div class="link-row__title">{{ $platform }}</div>
                <div class="link-row__actions">
                    <input type="text" class="input" value="{{ $url }}" readonly id="link-{{ $loop->index }}">
                    <button class="btn" data-copy="#link-{{ $loop->index }}">📋 Copiază</button>
                    <a class="btn btn-outline" href="{{ $url }}" target="_blank" rel="noopener">Deschide</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div>📭</div>
                <h3>Nu există link-uri pregenerate</h3>
                <p class="text-muted">Adaugă link-uri în controller pentru a le testa rapid.</p>
            </div>
        @endforelse
    </section>

    <section class="card card-info">
        <h3 class="card-title">Sfaturi de test</h3>
        <ul class="list">
            <li>Nu folosi <code>rel="noreferrer"</code> pe linkuri, altfel <em>Referer</em> nu ajunge la server.</li>
            <li>Dacă testezi din CodePen/JSFiddle, deschide „View”/„Open in new window” ca să nu fie în iframe.</li>
            <li>UTM-urile apar doar dacă linkul are parametri (ex: <code>?utm_source=facebook</code>).</li>
        </ul>
    </section>
</main>

<script>
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        const selector = btn.getAttribute('data-copy');
        const el = document.querySelector(selector);
        if (!el) return;

        try {
            await navigator.clipboard.writeText(el.value);
            btn.textContent = '✅ Copiat';
            setTimeout(() => btn.textContent = '📋 Copiază', 1200);
        } catch {
            el.select();
            document.execCommand('copy');
            alert('Link copiat în clipboard!');
        }
    });
</script>
</body>
</html>
