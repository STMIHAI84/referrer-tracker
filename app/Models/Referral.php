<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Referral extends Model
{
    protected $fillable = [
        'referrer_url',
        'referrer_host',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referral_code',
        'landing_path',
        'ip',
        'user_agent',
        'full_url'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function referrerDomain(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->referrer_host ? preg_replace('/^www\./', '', $this->referrer_host) : null
        );
    }

    public function scopeExternal($query)
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        return $query->where('referrer_host', '!=', $appHost)
            ->whereNotNull('referrer_host');
    }

    public function scopeFromDomain($query, string $domain)
    {
        return $query->where('referrer_host', 'like', "%{$domain}%");
    }

    // Scope nou pentru filtrare sursă
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    // Scope nou pentru filtrare UTM
    public function scopeWithUtm($query, $utmSource)
    {
        return $query->where('utm_source', $utmSource);
    }
}
