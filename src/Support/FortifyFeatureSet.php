<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

use Closure;
use Laravel\Fortify\Features;

class FortifyFeatureSet
{
    /**
     * @param array<string, mixed> $features
     *
     * @return array<int, string|array<string, mixed>>
     */
    public static function fromConfig(array $features): array
    {
        $resolved = [];

        foreach (self::simpleFeatures() as $key => $feature) {
            if (true === ($features[$key] ?? false)) {
                $resolved[] = $feature();
            }
        }

        $twoFactor = self::twoFactor($features['two-factor-authentication'] ?? false);

        if (null !== $twoFactor) {
            $resolved[] = $twoFactor;
        }

        return $resolved;
    }

    /**
     * @return array<string, Closure(): string>
     */
    private static function simpleFeatures(): array
    {
        return [
            'registration'               => Features::registration(...),
            'reset-passwords'            => Features::resetPasswords(...),
            'email-verification'         => Features::emailVerification(...),
            'update-profile-information' => Features::updateProfileInformation(...),
            'update-passwords'           => Features::updatePasswords(...),
        ];
    }

    private static function twoFactor(mixed $twoFactor): ?string
    {
        if (true === $twoFactor) {
            return Features::twoFactorAuthentication();
        }

        if (
            is_array($twoFactor)
            && true === ($twoFactor['enabled'] ?? false)
        ) {
            return Features::twoFactorAuthentication([
                'confirm'         => $twoFactor['confirm']          ?? true,
                'confirmPassword' => $twoFactor['confirm_password'] ?? true,
            ]);
        }

        return null;
    }
}
