<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use ValueError;
use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;
use Zairakai\LaravelAuth\Support\Punycode;

class NativeDnsRecordChecker implements DnsRecordChecker
{
    public function hasRecords(string $domain, string $type): bool
    {
        // checkdnsrr() does not read an international domain: it needs its ASCII form
        $host = Punycode::toAscii(trim($domain));

        if ('' === $host) {
            return false;
        }

        try {
            return 'MX' === strtoupper($type)
                ? $this->hasMailServer($host)
                : checkdnsrr($host, $type);
        }
        catch (ValueError) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    protected function mailServers(string $host): array
    {
        $servers = [];

        return getmxrr($host, $servers)
            ? $servers
            : [];
    }

    /**
     * A null MX (RFC 7505, the target is ".") says that the domain takes no mail: it does not count as a mail server.
     */
    private function hasMailServer(string $host): bool
    {
        return array_any(
            $this->mailServers($host),
            static fn (string $server): bool => '' !== rtrim(trim($server), '.'),
        );
    }
}
