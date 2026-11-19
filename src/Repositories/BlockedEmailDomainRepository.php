<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Services\EmailDomainNormalizer;

/**
 * The blocklist is one cache entry: the normalized domains of the table. Any change of the table rebuilds it, and
 * refresh() does the same after a bulk query, which fires no model event.
 */
class BlockedEmailDomainRepository
{
    public function __construct(
        protected CacheRepository $cacheRepository,
        protected EmailDomainNormalizer $emailDomainNormalizer,
    ) {}

    public function contains(string $domain): bool
    {
        $normalizedDomain = $this->emailDomainNormalizer->normalizeDomain($domain);

        if ('' === $normalizedDomain) {
            return false;
        }

        return isset($this->domains()[$normalizedDomain]);
    }

    /**
     * Clears the cached blocklist and fills it again from the table.
     */
    public function refresh(): void
    {
        $this->cacheRepository->forget($this->cacheKey());

        $this->domains();
    }

    protected function cacheKey(): string
    {
        $prefixConfig = config(
            'laravel-auth.email_filter.cache_prefix',
            'laravel-auth:blocked-email-domain:',
        );
        $prefix = is_string($prefixConfig)
            ? $prefixConfig
            : 'laravel-auth:blocked-email-domain:';

        return sprintf('%s%s', $prefix, 'all');
    }

    protected function cacheTtl(): int
    {
        $ttl = config('laravel-auth.email_filter.cache_ttl', 86400);

        return is_int($ttl)
            ? $ttl
            : 86400;
    }

    /**
     * @return array<string, true>
     */
    protected function domains(): array
    {
        // The cache holds what fetchDomains() returned
        return $this->cacheRepository->remember(
            $this->cacheKey(),
            $this->cacheTtl(),
            $this->fetchDomains(...),
        );
    }

    /**
     * @return array<string, true>
     */
    protected function fetchDomains(): array
    {
        return array_fill_keys(
            array_filter(
                BlockedEmailDomain::query()->toBase()->pluck('normalized_domain')->all(),
                is_string(...),
            ),
            true,
        );
    }
}
