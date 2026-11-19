<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;
use Zairakai\LaravelAuth\Services\EmailDomainDnsChecker;

final class EmailDomainDnsCheckerTest extends TestCase
{
    #[Test]
    public function it_accepts_domains_with_an_mx_record(): void
    {
        $emailDomainDnsChecker = new EmailDomainDnsChecker($this->fakeDns([
            'gmail.com' => ['MX' => true],
        ]));

        self::assertTrue($emailDomainDnsChecker->canReceiveMail('gmail.com'));
    }

    #[Test]
    public function it_asks_for_a_mail_server_only_by_default(): void
    {
        $emailDomainDnsChecker = new EmailDomainDnsChecker($this->fakeDns([
            'parked.example' => ['MX' => false, 'A' => true, 'AAAA' => true],
        ]));

        self::assertFalse($emailDomainDnsChecker->canReceiveMail('parked.example'));
    }

    #[Test]
    public function it_falls_back_to_a_records_when_they_are_allowed(): void
    {
        $emailDomainDnsChecker = new EmailDomainDnsChecker($this->fakeDns([
            'gmail.fr' => ['MX' => false, 'A' => true],
        ]), ['MX', 'A', 'AAAA']);

        self::assertTrue($emailDomainDnsChecker->canReceiveMail('gmail.fr'));
    }

    #[Test]
    public function it_rejects_domains_without_supported_dns_records(): void
    {
        $emailDomainDnsChecker = new EmailDomainDnsChecker($this->fakeDns([
            'gamil.com' => ['MX' => false, 'A' => false, 'AAAA' => false],
        ]));

        self::assertFalse($emailDomainDnsChecker->canReceiveMail('gamil.com'));
    }

    /**
     * @param array<string, array<string, bool>> $records
     */
    protected function fakeDns(array $records): DnsRecordChecker
    {
        return new class($records) implements DnsRecordChecker
        {
            /**
             * @param array<string, array<string, bool>> $records
             */
            public function __construct(
                protected array $records,
            ) {}

            public function hasRecords(string $domain, string $type): bool
            {
                return $this->records[$domain][$type] ?? false;
            }
        };
    }
}
