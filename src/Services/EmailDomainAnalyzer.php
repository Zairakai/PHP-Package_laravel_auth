<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

use Zairakai\LaravelAuth\Support\Punycode;

/**
 * Looks at the characters of a domain and names what is suspicious in them (lookalike domains).
 */
class EmailDomainAnalyzer
{
    /**
     * Indicators that the analysis sets by itself. Any other indicator comes from a manual choice.
     */
    public const array DETECTABLE = [
        'non_ascii',
        'cyrillic_characters',
        'greek_characters',
        'mixed_scripts',
        'punycode',
    ];

    /**
     * Alphabets that a lookalike domain mixes. Han, Hiragana and Katakana count as one: Japanese uses them together.
     */
    private const array SCRIPTS = [
        'latin'      => '/\p{Latin}/u',
        'cyrillic'   => '/\p{Cyrillic}/u',
        'greek'      => '/\p{Greek}/u',
        'armenian'   => '/\p{Armenian}/u',
        'hebrew'     => '/\p{Hebrew}/u',
        'arabic'     => '/\p{Arabic}/u',
        'hangul'     => '/\p{Hangul}/u',
        'thai'       => '/\p{Thai}/u',
        'devanagari' => '/\p{Devanagari}/u',
        'cjk'        => '/[\p{Han}\p{Hiragana}\p{Katakana}\p{Bopomofo}]/u',
    ];

    /**
     * @return array<int, string>
     */
    public function indicators(string $domain): array
    {
        $indicators = [];

        foreach ($this->checks(mb_strtolower(trim($domain))) as $indicator => $detected) {
            if ($detected) {
                $indicators[] = $indicator;
            }
        }

        return $indicators;
    }

    /**
     * Keeps the indicators chosen by hand, drops the ones of an earlier analysis and adds the ones of this domain.
     *
     * @param array<int, string> $current
     *
     * @return array<int, string>
     */
    public function merge(array $current, string $domain): array
    {
        return array_values(array_unique([
            ...array_diff($current, self::DETECTABLE),
            ...$this->indicators($domain),
        ]));
    }

    /**
     * @return array<string, bool>
     */
    private function checks(string $domain): array
    {
        // A domain given in punycode is read in Unicode, as a person sees it (with the intl extension)
        $unicode = Punycode::toUnicode($domain);

        return [
            'non_ascii'           => 1 === preg_match('/[^\x00-\x7F]/', $unicode),
            'cyrillic_characters' => 1 === preg_match('/\p{Cyrillic}/u', $unicode),
            'greek_characters'    => 1 === preg_match('/\p{Greek}/u', $unicode),
            'mixed_scripts'       => array_any(explode('.', $unicode), $this->mixesScripts(...)),
            'punycode'            => array_any(
                explode('.', $domain),
                static fn (string $label): bool => str_starts_with($label, 'xn--'),
            ),
        ];
    }

    private function mixesScripts(string $label): bool
    {
        $found = array_filter(
            self::SCRIPTS,
            static fn (string $pattern): bool => 1 === preg_match($pattern, $label),
        );

        return count($found) > 1;
    }
}
