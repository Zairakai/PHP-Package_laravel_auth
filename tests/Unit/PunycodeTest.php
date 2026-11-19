<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zairakai\LaravelAuth\Support\Punycode;

final class PunycodeTest extends TestCase
{
    /**
     * Expected values come from the punycode codec of Python (RFC 3492), not from this class.
     *
     * @return iterable<string, array{string, string}>
     */
    public static function domains(): iterable
    {
        yield 'ascii is left as it is' => ['gmail.com', 'gmail.com'];

        yield 'a cyrillic a in gmail' => ["gm\u{0430}il.com", 'xn--gmil-63d.com'];

        yield 'a latin accent' => ['bücher.example', 'xn--bcher-kva.example'];

        yield 'a cyrillic domain and extension' => ['пример.рф', 'xn--e1afmkfd.xn--p1ai'];

        yield 'a greek omicron in google' => ["g\u{03BF}ogle.com", 'xn--gogle-rce.com'];

        yield 'a tilde' => ['mañana.es', 'xn--maana-pta.es'];

        yield 'han' => ['日本語.jp', 'xn--wgv71a119e.jp'];

        yield 'already encoded' => ['xn--gmil-63d.com', 'xn--gmil-63d.com'];
    }

    #[Test]
    #[DataProvider('domains')]
    public function it_encodes_each_label_that_is_not_ascii_without_the_intl_extension(string $domain, string $expected): void
    {
        self::assertSame($expected, Punycode::toAsciiWithoutIntl($domain));
    }

    #[Test]
    #[DataProvider('domains')]
    public function it_gives_the_same_form_with_the_intl_extension(string $domain, string $expected): void
    {
        if (! function_exists('idn_to_ascii')) {
            self::markTestSkipped('The intl extension is not loaded.');
        }

        self::assertSame($expected, Punycode::toAscii($domain));
    }

    #[Test]
    public function it_maps_capitals_and_full_width_characters_with_the_intl_extension(): void
    {
        if (! function_exists('idn_to_ascii')) {
            self::markTestSkipped('The intl extension is not loaded.');
        }

        self::assertSame('xn--gmil-63d.com', Punycode::toAscii("GM\u{0410}IL.com"));
        self::assertSame('gmail.com', Punycode::toAscii("\u{FF47}mail.com"));
    }

    #[Test]
    public function it_returns_an_empty_domain_as_it_is(): void
    {
        self::assertSame('', Punycode::toAscii(''));
        self::assertSame('', Punycode::toUnicode(''));
        self::assertSame('', Punycode::toAsciiWithoutIntl(''));
    }
}
