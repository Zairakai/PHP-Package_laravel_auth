<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Services;

class EmailDomainNormalizer
{
    public function fromEmail(string $email): ?string
    {
        $normalizedEmail = mb_strtolower(trim($email));

        if ('' === $normalizedEmail || ! str_contains($normalizedEmail, '@')) {
            return null;
        }

        $parts  = explode('@', $normalizedEmail);
        $domain = end($parts);

        if (! is_string($domain)) {
            return null;
        }

        $normalizedDomain = trim($domain);

        return '' !== $normalizedDomain ? $normalizedDomain : null;
    }
}
