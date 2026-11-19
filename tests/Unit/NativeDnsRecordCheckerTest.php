<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Services\NativeDnsRecordChecker;

final class NativeDnsRecordCheckerTest extends TestCase
{
    #[Test]
    public function it_answers_false_for_a_domain_that_cannot_exist_without_a_query(): void
    {
        self::assertFalse(new NativeDnsRecordChecker()->hasRecords(str_repeat('a', 300) . '.invalid', 'A'));
    }

    #[Test]
    public function it_answers_false_for_an_empty_domain_instead_of_failing(): void
    {
        self::assertFalse(new NativeDnsRecordChecker()->hasRecords('', 'A'));
        self::assertFalse(new NativeDnsRecordChecker()->hasRecords('   ', 'MX'));
    }

    #[Test]
    public function it_answers_false_when_the_domain_has_no_mx(): void
    {
        self::assertFalse($this->checkerWithServers([])->hasRecords('example.com', 'MX'));
    }

    #[Test]
    public function it_counts_a_real_mail_server_as_an_mx(): void
    {
        self::assertTrue($this->checkerWithServers(['mx1.example.com', 'mx2.example.com'])->hasRecords('example.com', 'MX'));
    }

    #[Test]
    public function it_does_not_count_a_null_mx_as_a_mail_server(): void
    {
        self::assertFalse($this->checkerWithServers(['.'])->hasRecords('example.com', 'MX'));
        self::assertFalse($this->checkerWithServers([''])->hasRecords('example.com', 'MX'));
    }

    /**
     * @param array<int, string> $servers
     */
    private function checkerWithServers(array $servers): NativeDnsRecordChecker
    {
        return new class($servers) extends NativeDnsRecordChecker
        {
            /**
             * @param array<int, string> $servers
             */
            public function __construct(private readonly array $servers) {}

            protected function mailServers(string $host): array
            {
                return $this->servers;
            }
        };
    }
}
