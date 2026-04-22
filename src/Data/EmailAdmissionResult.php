<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Data;

class EmailAdmissionResult
{
    public function __construct(
        public bool $allowed,
        public ?string $reason = null,
    ) {}

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $reason): self
    {
        return new self(false, $reason);
    }
}
