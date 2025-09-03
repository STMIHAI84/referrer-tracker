@extends('layouts.app')

@section('title', 'Admin Referrers')
@section('page-title', 'Referrers recenți')

@section('header-buttons')
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('landing') }}" class="btn">🏠 Landing page</a>
        <a href="{{ route('generate.links') }}" class="btn btn-secondary">🔗 Generator Link-uri</a>
        <a href="{{ route('test.page1') }}" class="btn btn-secondary">🧪 Test 1</a>
        <a href="{{ route('test.page2') }}" class="btn btn-secondary">🧪 Test 2</a>
        <a href="{{ route('admin.referrers.export') }}" class="btn btn-secondary">📥 Exportă CSV</a>

    </div>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-number">{{ $items->count() }}</div>
            <div class="stat-label">Total înregistrări</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🌐</div>
            <div class="stat-number">{{ $items->unique('source')->count() }}</div>
            <div class="stat-label">Surse unice</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💻</div>
            <div class="stat-number">{{ $items->unique('ip')->count() }}</div>
            <div class="stat-label">IP-uri unice</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-number">{{ $items->where('source', 'direct')->count() }}</div>
            <div class="stat-label">Trafic direct</div>
        </div>
    </div>
    <div class="card">
        <h3>Statistici surse</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            @php
                $sourceCounts = $items->groupBy('source')->map->count()->sortDesc();
            @endphp
            @foreach($sourceCounts as $source => $count)
                <div style="padding: 1rem; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <strong>{{ ucfirst($source) }}</strong>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #4361ee;">{{ $count }}</div>
                    <small>{{ round(($count / $items->count()) * 100, 1) }}%</small>
                </div>
            @endforeach
        </div>
    </div>

    @if($items->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Creat la</th>
                    <th>Sursa</th>
                    <th>UTM Source</th>
                    <th>UTM Medium</th>
                    <th>UTM Campaign</th>
                    <th>Host Referrer</th>
                    <th>Landing</th>
                    <th>IP</th>
                    <th>User-Agent</th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $r)
                    <tr>
                        <td><span class="badge badge-primary">#{{ $r->id }}</span></td>
                        <td class="timestamp">{{ $r->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <span class="badge" style="background:
                                @if($r->source == 'facebook') #1877F2
                                @elseif($r->source == 'instagram') #E4405F
                                @elseif($r->source == 'whatsapp') #25D366
                                @elseif($r->source == 'twitter') #1DA1F2
                                @elseif($r->source == 'direct') #6c757d
                                @else #4361ee @endif">
                                {{ $r->source ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $r->utm_source ?? '—' }}</td>
                        <td>{{ $r->utm_medium ?? '—' }}</td>
                        <td>{{ $r->utm_campaign ?? '—' }}</td>
                        <td>{{ $r->referrer_host ?? '—' }}</td>
                        <td>{{ $r->landing_path }}</td>
                        <td>{{ $r->ip }}</td>
                        <td class="user-agent-cell">{{ Str::limit($r->user_agent, 50) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Statistici surse</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                @php
                    $sourceCounts = $items->groupBy('source')->map->count()->sortDesc();
                @endphp
                @foreach($sourceCounts as $source => $count)
                    <div style="padding: 1rem; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <strong>{{ ucfirst($source) }}</strong>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #4361ee;">{{ $count }}</div>
                        <small>{{ round(($count / $items->count()) * 100, 1) }}%</small>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="empty-state">
            <div>📊</div>
            <h3>Nu există referrers înregistrați</h3>
            <p>Încă nu s-au înregistrat referrers. Încearcă să accesezi pagina landing din alte surse.</p>
            <div style="margin-top: 1rem;">
                <a href="{{ route('generate.links') }}" class="btn">🔗 Generează link-uri de test</a>
            </div>
        </div>
    @endif
    {{ $items->links('pagination::bootstrap-5') }}

@endsection

<style>
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 500;
        color: white;
    }
</style>
