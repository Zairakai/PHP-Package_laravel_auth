<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Contracts;

interface DnsRecordChecker
{
    public function hasRecords(string $domain, string $type): bool;
}
