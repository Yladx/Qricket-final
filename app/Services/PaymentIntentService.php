<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PaymentIntentService
{
    private string $prefix = 'payment_intent:';
    private int $ttlSeconds;

    public function __construct(int $ttlSeconds = 86400)
    {
        $this->ttlSeconds = $ttlSeconds; // default 24h
    }

    public function store(string $externalId, array $data): void
    {
        Cache::put($this->key($externalId), $data, $this->ttlSeconds);
    }

    public function get(string $externalId): ?array
    {
        return Cache::get($this->key($externalId));
    }

    public function forget(string $externalId): void
    {
        Cache::forget($this->key($externalId));
    }

    public function exists(string $externalId): bool
    {
        return Cache::has($this->key($externalId));
    }

    private function key(string $externalId): string
    {
        return $this->prefix.$externalId;
    }
}


