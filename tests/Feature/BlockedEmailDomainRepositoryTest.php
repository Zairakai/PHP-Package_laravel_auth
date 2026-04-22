<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Repositories\BlockedEmailDomainRepository;
use Zairakai\LaravelAuth\Tests\TestCase;

final class BlockedEmailDomainRepositoryTest extends TestCase
{
    #[Test]
    public function it_detects_blocked_domains_from_the_database(): void
    {
        BlockedEmailDomain::query()->create([
            'domain' => 'mailinator.com',
        ]);

        $blockedEmailDomainRepository = $this->app->make(BlockedEmailDomainRepository::class);

        $this->assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));
        $this->assertFalse($blockedEmailDomainRepository->contains('example.com'));
    }
}
