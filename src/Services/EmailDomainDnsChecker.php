<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;

class EmailDomainDnsChecker
{
    /**
     * @param array<int, string> $recordTypes the records that prove the domain can receive mail (one is enough)
     */
    public function __construct(
        protected DnsRecordChecker $dnsRecordChecker,
        protected array $recordTypes = ['MX'],
    ) {}

    public function canReceiveMail(string $domain): bool
    {
        $normalizedDomain = mb_strtolower(trim($domain));

        if ('' === $normalizedDomain) {
            return false;
        }

        return array_any(
            $this->recordTypes,
            fn (string $recordType): bool => $this->dnsRecordChecker->hasRecords($normalizedDomain, $recordType),
        );
    }
}
