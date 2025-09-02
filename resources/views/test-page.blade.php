@extends('layouts.app')

@section('title', 'Pagina de Test')
@section('page-title', 'Pagina de Test #{{ $page }}')

@section('header-buttons')
    <a href="{{ route('landing') }}" class="btn">← Înapoi la landing page</a>
@endsection

@section('content')
    <div class="card card-info">
        <h2>Pagina de test #{{ $page }}</h2>
        <p>Această pagină este folosită pentru testarea funcționalității de tracking.</p>
        <p class="text-muted">Când apeși pe butonul de mai jos, header-ul Referer va fi setat la această pagină.</p>
    </div>

    <div class="card">
        <h3>Testează acum:</h3>
        <p>Apasă butonul de mai jos pentru a reveni la pagina landing cu referrer setat:</p>

        <div class="mt-3">
            <a href="{{ url('/landing') }}" class="btn">👉 Testează tracking</a>
        </div>

        <div class="mt-3">
            <p class="text-muted">Sau copiază și folosește comanda curl:</p>
            <pre style="background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow: auto;">
curl -H "Referer: {{ url()->current() }}" {{ url('/landing') }}</pre>
        </div>
    </div>

    <div class="card">
        <h3>Informații despre test:</h3>
        <p><strong>URL curent:</strong> {{ url()->current() }}</p>
        <p><strong>Host:</strong> {{ parse_url(url()->current(), PHP_URL_HOST) }}</p>
        <p class="text-muted">Acest referrer ar trebui să fie detectat ca intern și să nu se înregistreze.</p>
    </div>
@endsection
