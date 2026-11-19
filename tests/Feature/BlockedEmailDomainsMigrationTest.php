<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\LaravelAuthServiceProvider;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Tests\TestCase;

final class BlockedEmailDomainsMigrationTest extends TestCase
{
    #[Test]
    public function it_can_be_published_again_over_the_same_file(): void
    {
        $paths = ServiceProvider::pathsToPublish(LaravelAuthServiceProvider::class, 'zairakai-migrations');

        self::assertCount(1, $paths);

        foreach ($paths as $source => $target) {
            self::assertIsString($target);
            self::assertSame(basename($source), basename($target));
        }
    }

    #[Test]
    public function it_creates_the_table_of_the_blocked_domains(): void
    {
        self::assertTrue(Schema::hasTable('blocked_email_domains'));
        self::assertTrue(Schema::hasColumns('blocked_email_domains', ['id', 'domain', 'normalized_domain', 'indicators']));
        self::assertFalse(Schema::hasColumn('blocked_email_domains', 'created_at'));
    }

    #[Test]
    public function it_refuses_the_same_domain_twice(): void
    {
        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);
    }
}
