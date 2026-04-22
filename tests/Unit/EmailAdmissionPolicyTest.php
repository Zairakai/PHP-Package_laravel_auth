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
    public function it_allows_registration_when_the_domain_passes_all_checks(): void
    {
        $this->mockDns(['gmail.com' => ['MX' => true]]);

        $emailAdmissionPolicy = $this->app->make(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@gmail.com', 'registration');

        $this->assertTrue($emailAdmissionResult->allowed);
        $this->assertNull($emailAdmissionResult->reason);
    }

    #[Test]
    public function it_rejects_registration_when_the_domain_has_no_dns_records(): void
    {
        $this->mockDns(['gamil.com' => ['MX' => false, 'A' => false, 'AAAA' => false]]);

        $emailAdmissionPolicy = $this->app->make(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@gamil.com', 'registration');

        $this->assertFalse($emailAdmissionResult->allowed);
        $this->assertSame('unresolvable_email_domain', $emailAdmissionResult->reason);
    }

    #[Test]
    public function it_rejects_registration_when_the_domain_is_blocked(): void
    {
        $this->mockDns(['mailinator.com' => ['MX' => true]]);

        BlockedEmailDomain::query()->create([
            'domain' => 'mailinator.com',
        ]);

        $emailAdmissionPolicy = $this->app->make(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@mailinator.com', 'registration');

        $this->assertFalse($emailAdmissionResult->allowed);
        $this->assertSame('blocked_email_domain', $emailAdmissionResult->reason);
    }

    #[Test]
    public function it_skips_login_checks_by_default(): void
    {
        $this->mockDns(['unknown.test' => ['MX' => false, 'A' => false, 'AAAA' => false]]);

        BlockedEmailDomain::query()->create([
            'domain' => 'unknown.test',
        ]);

        $emailAdmissionPolicy = $this->app->make(EmailAdmissionPolicy::class);
        $emailAdmissionResult = $emailAdmissionPolicy->inspect('john@unknown.test', 'login');

        $this->assertTrue($emailAdmissionResult->allowed);
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

        $this->app->instance(DnsRecordChecker::class, $mock);
    }
}
