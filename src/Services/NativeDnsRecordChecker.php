<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;

class NativeDnsRecordChecker implements DnsRecordChecker
{
    public function hasRecords(string $domain, string $type): bool
    {
        return checkdnsrr($domain, $type);
    }
}
