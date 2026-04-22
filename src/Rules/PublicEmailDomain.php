<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Zairakai\LaravelAuth\Services\EmailAdmissionPolicy;

class PublicEmailDomain implements ValidationRule
{
    public function __construct(
        protected EmailAdmissionPolicy $emailAdmissionPolicy,
        protected string $context = 'registration',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || '' === trim($value)) {
            return;
        }

        $emailAdmissionResult = $this->emailAdmissionPolicy->inspect($value, $this->context);

        if (! $emailAdmissionResult->allowed && null !== $emailAdmissionResult->reason) {
            $fail($emailAdmissionResult->reason);
        }
    }
}
