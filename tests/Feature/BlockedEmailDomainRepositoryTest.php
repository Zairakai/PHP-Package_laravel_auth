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

        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));
        self::assertFalse($blockedEmailDomainRepository->contains('example.com'));
    }

    #[Test]
    public function it_does_not_see_a_bulk_delete_before_the_blocklist_is_refreshed(): void
    {
        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);

        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));

        BlockedEmailDomain::query()->delete();

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));

        $blockedEmailDomainRepository->refresh();

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));
    }

    #[Test]
    public function it_does_not_see_a_bulk_insert_before_the_blocklist_is_refreshed(): void
    {
        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));

        BlockedEmailDomain::query()->toBase()->insert(['domain' => 'mailinator.com', 'normalized_domain' => 'mailinator.com']);

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));

        $blockedEmailDomainRepository->refresh();

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));
    }

    #[Test]
    public function it_keeps_the_whole_blocklist_in_one_cache_entry(): void
    {
        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);
        BlockedEmailDomain::query()->create(['domain' => 'spam.example']);

        $cached = cache('laravel-auth:blocked-email-domain:all');

        self::assertSame(['mailinator.com' => true, 'spam.example' => true], $cached);
    }
}
