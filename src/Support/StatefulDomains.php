<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

class StatefulDomains
{
    /**
     * @return array<int, string>
     */
    public static function parse(string $domains): array
    {
        if ('' === $domains) {
            return [];
        }

        $parsed = array_map(
            trim(...),
            explode(',', $domains),
        );

        return array_values(array_filter(
            $parsed,
            static fn (string $domain): bool => '' !== $domain,
        ));
    }
}
