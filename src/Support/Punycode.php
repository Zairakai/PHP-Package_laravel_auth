<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

/**
 * Converts a domain to its ASCII form (punycode, the one that DNS reads) and back.
 *
 * With the intl extension the conversion follows UTS #46: case and compatibility characters are mapped too, so a domain
 * typed in capitals, full-width or in punycode gives one form. Without it a built-in encoder (RFC 3492) takes over: it
 * encodes the labels but applies no mapping and is not infallible. Enabling the intl extension is strongly recommended.
 */
class Punycode
{
    public static function toAscii(string $domain): string
    {
        if ('' === $domain) {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if (is_string($ascii) && '' !== $ascii) {
                return $ascii;
            }
        }

        return self::toAsciiWithoutIntl($domain);
    }

    /**
     * The built-in encoder, the fallback of toAscii().
     */
    public static function toAsciiWithoutIntl(string $domain): string
    {
        return PunycodeEncoder::encodeDomain($domain);
    }

    /**
     * The domain in Unicode when it is encoded in punycode. Without the intl extension it is returned as it is.
     */
    public static function toUnicode(string $domain): string
    {
        if ('' === $domain) {
            return '';
        }

        if (function_exists('idn_to_utf8')) {
            $unicode = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if (is_string($unicode) && '' !== $unicode) {
                return $unicode;
            }
        }

        return $domain;
    }
}
