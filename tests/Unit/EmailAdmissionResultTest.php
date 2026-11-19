<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Data\EmailAdmissionResult;

final class EmailAdmissionResultTest extends TestCase
{
    #[Test]
    public function it_allows_without_a_reason(): void
    {
        $result = EmailAdmissionResult::allow();

        self::assertTrue($result->allowed);
        self::assertNull($result->reason);
    }

    #[Test]
    public function it_denies_with_its_reason(): void
    {
        $result = EmailAdmissionResult::deny('blocked_email_domain');

        self::assertFalse($result->allowed);
        self::assertSame('blocked_email_domain', $result->reason);
    }
}
