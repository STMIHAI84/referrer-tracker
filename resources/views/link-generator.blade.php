<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Generator Link-uri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; }
        a { color: #2196F3; text-decoration: none; }
        a:hover { text-decoration: underline; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 0.5rem; }
        button { background: #4361ee; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<a href="{{ route('landing') }}">← Înapoi la Landing</a>
<a href="{{ route('admin.referrers') }}" style="margin-left: 1rem;">📊 Admin</a>
<a href="https://referrer-tracker.onrender.com/landing" target="_blank">Test referrer</a>

<h1>Generator Link-uri de Tracking</h1>

<div class="card">
    <h3>Link-uri pregenerate:</h3>
    @foreach($links as $platform => $url)
        <div style="margin-bottom: 1.5rem;">
            <h4>{{ $platform }}</h4>
            <input type="text" value="{{ $url }}" readonly id="link-{{ $loop->index }}">
            <button onclick="copyToClipboard('link-{{ $loop->index }}')">📋 Copiază</button>
        </div>
    @endforeach
</div>

<script>
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        element.select();
        document.execCommand('copy');
        alert('Link copiat în clipboard!');
    }
</script>
</body>
</html>
