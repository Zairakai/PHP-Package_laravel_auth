<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Rules\PublicEmailDomain;
use Zairakai\LaravelAuth\Services\EmailAdmissionPolicy;
use Zairakai\LaravelAuth\Services\EmailDomainDnsChecker;
use Zairakai\LaravelAuth\Tests\TestCase;

final class PublicEmailDomainTest extends TestCase
{
    #[Test]
    public function it_does_not_apply_to_a_context_that_is_off(): void
    {
        $this->resolveDnsTo(false);

        self::assertSame([], $this->errors('john@gamil.com', 'login'));
    }

    #[Test]
    public function it_fails_with_the_reason_of_the_policy(): void
    {
        $this->resolveDnsTo(true);
        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);

        self::assertSame(['blocked_email_domain'], $this->errors('john@mailinator.com'));

        $this->resolveDnsTo(false);

        self::assertSame(['unresolvable_email_domain'], $this->errors('john@gamil.com'));
    }

    #[Test]
    public function it_leaves_an_empty_or_non_string_value_to_the_other_rules(): void
    {
        self::assertSame([], $this->errors(''));
        self::assertSame([], $this->errors(['john@gamil.com']));
    }

    #[Test]
    public function it_passes_a_domain_that_receives_mail_and_is_not_blocked(): void
    {
        $this->resolveDnsTo(true);

        self::assertSame([], $this->errors('john@gmail.com'));
    }

    /**
     * @return array<array<string>|string>
     */
    private function errors(mixed $value, string $context = 'registration'): array
    {
        $validator = Validator::make(
            ['email' => $value],
            ['email' => [resolve(PublicEmailDomain::class, ['context' => $context])]],
        );

        return $validator->errors()->get('email');
    }

    private function resolveDnsTo(bool $answer): void
    {
        app()->instance(DnsRecordChecker::class, new readonly class($answer) implements DnsRecordChecker
        {
            public function __construct(private bool $answer) {}

            public function hasRecords(string $domain, string $type): bool
            {
                return $this->answer;
            }
        });

        app()->forgetInstance(EmailDomainDnsChecker::class);
        app()->forgetInstance(EmailAdmissionPolicy::class);
    }
}
