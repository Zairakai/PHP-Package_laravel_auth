<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

/**
 * Punycode encoder (RFC 3492) for the labels of a domain. It encodes and applies no mapping: Punycode uses it only
 * when the intl extension is missing.
 */
class PunycodeEncoder
{
    public static function encodeDomain(string $domain): string
    {
        return implode('.', array_map(
            self::encodeLabel(...),
            explode('.', $domain),
        ));
    }

    /**
     * @param array<int, int> $codePoints
     */
    private static function encodeExtended(array $codePoints, int $basicCount): string
    {
        $output  = '';
        $handled = $basicCount;
        $n       = PunycodeNumber::INITIAL_N;
        $delta   = 0;
        $bias    = PunycodeNumber::INITIAL_BIAS;

        while (null !== ($next = self::smallestFrom($codePoints, $n))) {
            $delta += ($next - $n) * ($handled + 1);
            $n      = $next;

            foreach ($codePoints as $codePoint) {
                if ($codePoint < $n) {
                    $delta++;
                }

                if ($codePoint === $n) {
                    $output .= PunycodeNumber::encodeDelta($delta, $bias);
                    $bias    = PunycodeNumber::adapt($delta, $handled + 1, $handled === $basicCount);
                    $delta   = 0;
                    $handled++;
                }
            }

            $delta++;
            $n++;
        }

        return $output;
    }

    private static function encodeLabel(string $label): string
    {
        if (1 !== preg_match('/[^\x00-\x7F]/', $label)) {
            return $label;
        }

        $codePoints = array_map(mb_ord(...), mb_str_split($label));
        $basic      = array_filter(
            $codePoints,
            static fn (int $codePoint): bool => PunycodeNumber::INITIAL_N > $codePoint,
        );
        $prefix     = [] === $basic
            ? ''
            : implode('', array_map(chr(...), $basic)) . '-';

        return 'xn--' . $prefix . self::encodeExtended($codePoints, count($basic));
    }

    /**
     * The smallest code point that is $n or more, or null when all of them are handled.
     *
     * @param array<int, int> $codePoints
     */
    private static function smallestFrom(array $codePoints, int $n): ?int
    {
        $remaining = array_filter($codePoints, static fn (int $codePoint): bool => $n <= $codePoint);

        return [] === $remaining
            ? null
            : min($remaining);
    }
}
