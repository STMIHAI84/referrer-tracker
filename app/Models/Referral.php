<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Referral extends Model
{
    protected $fillable = [
        'referrer_url','referrer_host','source',
        'utm_source','utm_medium','utm_campaign',
        'referral_code','landing_path','ip','user_agent','full_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function scopeExternal(Builder $q): Builder
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        return $q->whereNotNull('referrer_host')
            ->when($appHost, fn($qq) => $qq->where('referrer_host', '!=', preg_replace('/^www\./','',$appHost)));
    }

    public function scopeFromDomain(Builder $q, string $domain): Builder
    {
        return $q->where('referrer_host', 'like', "%{$domain}%");
    }

    public function scopeFromSource(Builder $q, string $source): Builder
    {
        return $q->where('source', $source);
    }

    public function scopeWithUtm(Builder $q, string $utmSource): Builder
    {
        return $q->where('utm_source', $utmSource);
    }

    public function scopeBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        return $q->when($from, fn($qq) => $qq->where('created_at','>=',$from))
            ->when($to,   fn($qq) => $qq->where('created_at','<=',$to));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $like = "%{$term}%";
        return $q->where(function($w) use ($like) {
            $w->where('referrer_host','like',$like)
                ->orWhere('ip','like',$like)
                ->orWhere('user_agent','like',$like)
                ->orWhere('source','like',$like)
                ->orWhere('utm_source','like',$like)
                ->orWhere('utm_medium','like',$like)
                ->orWhere('utm_campaign','like',$like);
        });
    }

    public function scopeExcludeBots(Builder $q, bool $exclude = false): Builder
    {
        return $exclude ? $q->where('source', 'not like', 'bot:%') : $q;
    }

    // === Accessors ===
    public function getReferrerDomainAttribute(): ?string
    {
        return $this->referrer_host ? preg_replace('/^www\./','',$this->referrer_host) : null;
    }
}
