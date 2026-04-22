<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;

class BlockedEmailDomainRepository
{
    public function __construct(
        protected CacheRepository $cacheRepository,
    ) {}

    public function contains(string $domain): bool
    {
        $normalizedDomain = mb_strtolower(trim($domain));

        if ('' === $normalizedDomain) {
            return false;
        }

        return $this->cacheRepository->remember(
            $this->cacheKey($normalizedDomain),
            $this->cacheTtl(),
            fn (): bool => BlockedEmailDomain::query()
                ->where('domain', $normalizedDomain)
                ->exists(),
        );
    }

    protected function cacheKey(string $domain): string
    {
        $prefixConfig = config(
            'laravel-auth.email_filter.cache_prefix',
            'laravel-auth:blocked-email-domain:',
        );
        $prefix = is_string($prefixConfig)
            ? $prefixConfig
            : 'laravel-auth:blocked-email-domain:';

        return sprintf('%s%s', $prefix, $domain);
    }

    protected function cacheTtl(): int
    {
        $ttl = config('laravel-auth.email_filter.cache_ttl', 86400);

        return is_int($ttl) ? $ttl : 86400;
    }
}
