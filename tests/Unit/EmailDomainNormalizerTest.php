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

        $this->assertSame('gmail.com', $emailDomainNormalizer->fromEmail(' John@GMAIL.COM '));
    }

    #[Test]
    public function it_returns_null_for_invalid_emails(): void
    {
        $emailDomainNormalizer = new EmailDomainNormalizer();

        $this->assertNull($emailDomainNormalizer->fromEmail('not-an-email'));
    }
}
