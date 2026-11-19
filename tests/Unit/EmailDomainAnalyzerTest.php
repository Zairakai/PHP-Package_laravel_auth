<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Services\EmailDomainAnalyzer;

final class EmailDomainAnalyzerTest extends TestCase
{
    /**
     * @return iterable<string, array{string, array<int, string>}>
     */
    public static function domains(): iterable
    {
        yield 'a plain domain' => ['gmail.com', []];

        yield 'a cyrillic a in gmail' => ["gm\u{0430}il.com", ['non_ascii', 'cyrillic_characters', 'mixed_scripts']];

        yield 'a greek omicron in google' => ["g\u{03BF}ogle.com", ['non_ascii', 'greek_characters', 'mixed_scripts']];

        yield 'a domain that is cyrillic from end to end' => ['пример.рф', ['non_ascii', 'cyrillic_characters']];

        yield 'a latin accent' => ['bücher.example', ['non_ascii']];

        yield 'japanese uses han and kana together' => ['日本語ひらがな.jp', ['non_ascii']];

    }

    #[Test]
    public function it_does_not_repeat_an_indicator(): void
    {
        $merged = new EmailDomainAnalyzer()->merge(['phishing', 'phishing'], 'gmail.com');

        self::assertSame(['phishing'], $merged);
    }

    #[Test]
    public function it_flags_a_domain_given_in_punycode(): void
    {
        self::assertContains('punycode', new EmailDomainAnalyzer()->indicators('xn--gmil-63d.com'));
    }

    #[Test]
    public function it_keeps_the_indicators_chosen_by_hand_and_replaces_the_detected_ones(): void
    {
        $merged = new EmailDomainAnalyzer()->merge(['phishing', 'cyrillic_characters'], 'bücher.example');

        self::assertSame(['phishing', 'non_ascii'], $merged);
    }

    /**
     * @param array<int, string> $expected
     */
    #[Test]
    #[DataProvider('domains')]
    public function it_names_what_is_suspicious_in_a_domain(string $domain, array $expected): void
    {
        self::assertSame($expected, new EmailDomainAnalyzer()->indicators($domain));
    }

    #[Test]
    public function it_reads_a_punycode_domain_as_a_person_sees_it(): void
    {
        if (! function_exists('idn_to_utf8')) {
            self::markTestSkipped('The intl extension is needed to read a punycode domain.');
        }

        self::assertSame(
            ['non_ascii', 'cyrillic_characters', 'mixed_scripts', 'punycode'],
            new EmailDomainAnalyzer()->indicators('xn--gmil-63d.com'),
        );
    }
}
