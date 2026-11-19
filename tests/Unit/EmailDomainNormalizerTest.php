<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Services\EmailDomainNormalizer;

final class EmailDomainNormalizerTest extends TestCase
{
    #[Test]
    public function it_extracts_and_normalizes_the_domain_from_an_email(): void
    {
        $emailDomainNormalizer = new EmailDomainNormalizer();

        self::assertSame('gmail.com', $emailDomainNormalizer->fromEmail(' John@GMAIL.COM '));
    }

    #[Test]
    public function it_gives_one_form_to_a_domain_in_unicode_or_in_punycode(): void
    {
        if (! function_exists('idn_to_ascii')) {
            self::markTestSkipped('The intl extension is not loaded.');
        }

        $emailDomainNormalizer = new EmailDomainNormalizer();

        self::assertSame('xn--gmil-63d.com', $emailDomainNormalizer->fromEmail("john@gm\u{0430}il.com"));
        self::assertSame('xn--gmil-63d.com', $emailDomainNormalizer->fromEmail("JOHN@GM\u{0410}IL.com"));
        self::assertSame('xn--gmil-63d.com', $emailDomainNormalizer->fromEmail('john@xn--gmil-63d.com'));
    }

    #[Test]
    public function it_returns_null_for_invalid_emails(): void
    {
        $emailDomainNormalizer = new EmailDomainNormalizer();

        self::assertNull($emailDomainNormalizer->fromEmail('not-an-email'));
    }
}
