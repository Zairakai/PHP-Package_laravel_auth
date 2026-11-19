<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Services\EmailAdmissionPolicy;
use Zairakai\LaravelAuth\Tests\TestCase;

final class EmailAdmissionPolicyTest extends TestCase
{
    #[Test]
    public function it_allows_a_lookalike_domain_that_is_not_blocked_and_receives_mail(): void
    {
        $this->mockDns(['xn--gmil-63d.com' => ['MX' => true]]);

        $emailAdmissionResult = resolve(EmailAdmissionPolicy::class)->inspect("john@gm\u{0430}il.com", 'registration');

        self::assertTrue($emailAdmissionResult->allowed);
    }

    #[Test]
    public function it_allows_an_international_domain_that_exists_and_receives_mail(): void
    {
        $this->mockDns(['xn--e1afmkfd.xn--p1ai' => ['MX' => true]]);

        $emailAdmissionResult = resolve(EmailAdmissionPolicy::class)->inspect('иван@пример.рф', 'registration');

        self::assertTrue($emailAdmissionResult->allowed);
        self::assertNull($emailAdmissionResult->reason);
    }

    #[Test]
    public function it_allows_registration_when_the_domain_passes_all_checks(): void
    {
        $this->mockDns(['gmail.com' => ['MX' => true]]);

        $emailAdmissionPolicy = resolve(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@gmail.com', 'registration');

        self::assertTrue($emailAdmissionResult->allowed);
        self::assertNull($emailAdmissionResult->reason);
    }

    #[Test]
    public function it_checks_the_blocklist_before_the_dns_so_that_a_blocked_domain_costs_no_query(): void
    {
        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);

        $checker = new class implements DnsRecordChecker
        {
            public int $queries = 0;

            public function hasRecords(string $domain, string $type): bool
            {
                $this->queries++;

                return false;
            }
        };

        app()->instance(DnsRecordChecker::class, $checker);

        $emailAdmissionResult = resolve(EmailAdmissionPolicy::class)->inspect('john@mailinator.com', 'registration');

        self::assertSame('blocked_email_domain', $emailAdmissionResult->reason);
        self::assertSame(0, $checker->queries);
    }

    #[Test]
    public function it_rejects_a_lookalike_domain_that_is_blocked_whatever_its_form(): void
    {
        BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com"]);

        $this->mockDns(['xn--gmil-63d.com' => ['MX' => true]]);

        foreach (["john@gm\u{0430}il.com", "JOHN@GM\u{0410}IL.COM", 'john@xn--gmil-63d.com'] as $email) {
            $emailAdmissionResult = resolve(EmailAdmissionPolicy::class)->inspect($email, 'registration');

            self::assertSame('blocked_email_domain', $emailAdmissionResult->reason, $email);
        }
    }

    #[Test]
    public function it_rejects_registration_when_the_domain_has_no_dns_records(): void
    {
        $this->mockDns(['gamil.com' => ['MX' => false, 'A' => false, 'AAAA' => false]]);

        $emailAdmissionPolicy = resolve(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@gamil.com', 'registration');

        self::assertFalse($emailAdmissionResult->allowed);
        self::assertSame('unresolvable_email_domain', $emailAdmissionResult->reason);
    }

    #[Test]
    public function it_rejects_registration_when_the_domain_is_blocked(): void
    {
        $this->mockDns(['mailinator.com' => ['MX' => true]]);

        BlockedEmailDomain::query()->create([
            'domain' => 'mailinator.com',
        ]);

        $emailAdmissionPolicy = resolve(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@mailinator.com', 'registration');

        self::assertFalse($emailAdmissionResult->allowed);
        self::assertSame('blocked_email_domain', $emailAdmissionResult->reason);
    }

    #[Test]
    public function it_skips_login_checks_by_default(): void
    {
        $this->mockDns(['unknown.test' => ['MX' => false, 'A' => false, 'AAAA' => false]]);

        BlockedEmailDomain::query()->create([
            'domain' => 'unknown.test',
        ]);

        $emailAdmissionPolicy = resolve(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@unknown.test', 'login');

        self::assertTrue($emailAdmissionResult->allowed);
    }

    /**
     * @param array<string, array<string, bool>> $records
     */
    protected function mockDns(array $records): void
    {
        $mock = new class($records) implements DnsRecordChecker
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

        app()->instance(DnsRecordChecker::class, $mock);
    }
}
