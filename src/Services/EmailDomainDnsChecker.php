<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;

class EmailDomainDnsChecker
{
    public function __construct(
        protected DnsRecordChecker $dnsRecordChecker,
    ) {}

    public function canReceiveMail(string $domain): bool
    {
        $normalizedDomain = mb_strtolower(trim($domain));

        if ('' === $normalizedDomain) {
            return false;
        }

        return array_any(['MX', 'A', 'AAAA'], fn (string $recordType): bool => $this->dnsRecordChecker->hasRecords($normalizedDomain, $recordType));
    }
}
