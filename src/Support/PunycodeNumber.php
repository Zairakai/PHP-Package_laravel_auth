<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

/**
 * The arithmetic of the punycode encoder (RFC 3492): variable-length integers and the adaptation of the bias.
 */
class PunycodeNumber
{
    public const int INITIAL_BIAS = 72;

    public const int INITIAL_N = 128;

    private const int BASE = 36;

    private const int DAMP = 700;

    private const int SKEW = 38;

    private const int TMAX = 26;

    private const int TMIN = 1;

    public static function adapt(int $delta, int $points, bool $first): int
    {
        $delta  = intdiv($delta, $first ? self::DAMP : 2);
        $delta += intdiv($delta, $points);
        $k      = 0;

        while (intdiv((self::BASE - self::TMIN) * self::TMAX, 2) < $delta) {
            $delta = intdiv($delta, self::BASE - self::TMIN);
            $k += self::BASE;
        }

        return $k + intdiv((self::BASE - self::TMIN + 1) * $delta, $delta + self::SKEW);
    }

    public static function encodeDelta(int $delta, int $bias): string
    {
        $output = '';
        $q      = $delta;

        for ($k = self::BASE; ; $k += self::BASE) {
            $threshold = self::threshold($k, $bias);

            if ($q < $threshold) {
                break;
            }

            $output .= self::digit($threshold + (($q - $threshold) % (self::BASE - $threshold)));
            $q       = intdiv($q - $threshold, self::BASE - $threshold);
        }

        return $output . self::digit($q);
    }

    private static function digit(int $value): string
    {
        return chr(26 > $value ? 97 + $value : 22 + $value);
    }

    private static function threshold(int $k, int $bias): int
    {
        if ($k <= $bias) {
            return self::TMIN;
        }

        return $bias + self::TMAX <= $k
            ? self::TMAX
            : $k - $bias;
    }
}
