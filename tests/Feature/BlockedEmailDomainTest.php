<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Repositories\BlockedEmailDomainRepository;
use Zairakai\LaravelAuth\Tests\TestCase;

final class BlockedEmailDomainTest extends TestCase
{
    #[Test]
    public function it_analyses_again_when_the_domain_changes(): void
    {
        $blockedEmailDomain = BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com", 'indicators' => ['spam']]);

        $blockedEmailDomain->update(['domain' => 'Example.com']);

        self::assertSame('example.com', $blockedEmailDomain->normalized_domain);
        self::assertSame(['spam'], $blockedEmailDomain->indicators);
    }

    #[Test]
    public function it_blocks_a_lookalike_domain_but_not_the_real_one(): void
    {
        BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com"]);

        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertTrue($blockedEmailDomainRepository->contains("GM\u{0410}IL.com"));
        self::assertFalse($blockedEmailDomainRepository->contains('gmail.com'));
    }

    #[Test]
    public function it_gives_the_indicators_in_the_language_of_the_application(): void
    {
        $blockedEmailDomain = BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com", 'indicators' => ['phishing', 'unknown_key']]);

        self::assertSame(
            [
                'Phishing',
                'unknown_key',
                'Non-ASCII characters',
                'Cyrillic characters (possible lookalike of a Latin domain)',
                'Several alphabets mixed in one label',
            ],
            $blockedEmailDomain->indicator_labels,
        );
    }

    #[Test]
    public function it_keeps_the_indicators_chosen_by_hand_next_to_the_detected_ones(): void
    {
        $blockedEmailDomain = BlockedEmailDomain::query()->create([
            'domain'     => "gm\u{0430}il.com",
            'indicators' => ['phishing'],
        ]);

        self::assertSame(['phishing', 'non_ascii', 'cyrillic_characters', 'mixed_scripts'], $blockedEmailDomain->indicators);
    }

    #[Test]
    public function it_refuses_the_same_domain_in_another_case(): void
    {
        BlockedEmailDomain::query()->create(['domain' => 'Mailinator.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        BlockedEmailDomain::query()->create(['domain' => 'mailinator.COM']);
    }

    #[Test]
    public function it_sets_the_indicators_of_a_lookalike_domain_by_analysis(): void
    {
        $blockedEmailDomain = BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com"]);

        self::assertSame(['non_ascii', 'cyrillic_characters', 'mixed_scripts'], $blockedEmailDomain->indicators);
    }

    #[Test]
    public function it_stores_the_domain_as_it_was_given_and_in_lowercase(): void
    {
        $blockedEmailDomain = BlockedEmailDomain::query()->create(['domain' => '  Mailinator.COM ']);

        self::assertSame('Mailinator.COM', $blockedEmailDomain->domain);
        self::assertSame('mailinator.com', $blockedEmailDomain->normalized_domain);
        self::assertSame([], $blockedEmailDomain->indicators);
    }

    #[Test]
    public function it_updates_the_cached_answer_when_a_domain_is_created(): void
    {
        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));

        BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));
    }

    #[Test]
    public function it_updates_the_cached_answer_when_a_domain_is_deleted(): void
    {
        $blockedEmailDomain           = BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);
        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));

        $blockedEmailDomain->delete();

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));
    }

    #[Test]
    public function it_updates_the_cached_answer_when_a_domain_is_renamed(): void
    {
        $blockedEmailDomain           = BlockedEmailDomain::query()->create(['domain' => 'mailinator.com']);
        $blockedEmailDomainRepository = resolve(BlockedEmailDomainRepository::class);

        self::assertTrue($blockedEmailDomainRepository->contains('mailinator.com'));
        self::assertFalse($blockedEmailDomainRepository->contains('example.com'));

        $blockedEmailDomain->update(['domain' => 'example.com']);

        self::assertFalse($blockedEmailDomainRepository->contains('mailinator.com'));
        self::assertTrue($blockedEmailDomainRepository->contains('example.com'));
    }
}
