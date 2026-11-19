<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Observers;

use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Repositories\BlockedEmailDomainRepository;
use Zairakai\LaravelAuth\Services\EmailDomainAnalyzer;
use Zairakai\LaravelAuth\Services\EmailDomainNormalizer;

/**
 * Keeps the normalized domain and the indicators of a blocked domain, and the cached blocklist, up to date.
 */
class BlockedEmailDomainObserver
{
    public function __construct(
        protected EmailDomainNormalizer $emailDomainNormalizer,
        protected EmailDomainAnalyzer $emailDomainAnalyzer,
        protected BlockedEmailDomainRepository $blockedEmailDomainRepository,
    ) {}

    public function deleted(BlockedEmailDomain $blockedEmailDomain): void
    {
        $this->blockedEmailDomainRepository->refresh();
    }

    public function saved(BlockedEmailDomain $blockedEmailDomain): void
    {
        $this->blockedEmailDomainRepository->refresh();
    }

    public function saving(BlockedEmailDomain $blockedEmailDomain): void
    {
        $blockedEmailDomain->domain            = trim($blockedEmailDomain->domain);
        $blockedEmailDomain->normalized_domain = $this->emailDomainNormalizer->normalizeDomain($blockedEmailDomain->domain);

        if ($blockedEmailDomain->isDirty('domain')) {
            $blockedEmailDomain->indicators = $this->emailDomainAnalyzer->merge(
                $blockedEmailDomain->indicators ?? [],
                $blockedEmailDomain->domain,
            );
        }
    }
}
