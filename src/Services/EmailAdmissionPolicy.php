<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use Zairakai\LaravelAuth\Data\EmailAdmissionResult;
use Zairakai\LaravelAuth\Repositories\BlockedEmailDomainRepository;

class EmailAdmissionPolicy
{
    public function __construct(
        protected EmailDomainNormalizer $emailDomainNormalizer,
        protected EmailDomainDnsChecker $emailDomainDnsChecker,
        protected BlockedEmailDomainRepository $blockedEmailDomainRepository,
    ) {}

    public function inspect(string $email, string $context = 'registration'): EmailAdmissionResult
    {
        if (
            ! $this->isEnabled()
            || ! $this->appliesTo($context)
        ) {
            return EmailAdmissionResult::allow();
        }

        $domain = $this->emailDomainNormalizer->fromEmail($email);

        if (null === $domain) {
            return EmailAdmissionResult::deny('invalid_email_domain');
        }

        $reason = $this->denialReason($domain);

        if (null !== $reason) {
            return EmailAdmissionResult::deny($reason);
        }

        return EmailAdmissionResult::allow();
    }

    protected function appliesTo(string $context): bool
    {
        return (bool) config(sprintf('laravel-auth.email_filter.apply_to.%s', $context), false);
    }

    protected function checksBlocklist(): bool
    {
        return (bool) config('laravel-auth.email_filter.check_blocklist', true);
    }

    protected function checksDns(): bool
    {
        return (bool) config('laravel-auth.email_filter.check_dns', true);
    }

    protected function denialReason(string $domain): ?string
    {
        // The blocklist is cached and light: it goes first, so that a blocked domain costs no DNS query
        if (
            $this->checksBlocklist()
            && $this->blockedEmailDomainRepository->contains($domain)
        ) {
            return 'blocked_email_domain';
        }

        if (
            $this->checksDns()
            && ! $this->emailDomainDnsChecker->canReceiveMail($domain)
        ) {
            return 'unresolvable_email_domain';
        }

        return null;
    }

    protected function isEnabled(): bool
    {
        return (bool) config('laravel-auth.email_filter.enabled', true);
    }
}
