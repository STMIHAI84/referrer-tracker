<?php

namespace App\Support;

class ReferralFilters
{
    public function __construct(
        public readonly ?string $source = null,
        public readonly ?string $utm_source = null,
        public readonly ?string $q = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly bool $exclude_bots = false,
        public readonly int $per_page = 50,
    ) {}
}
