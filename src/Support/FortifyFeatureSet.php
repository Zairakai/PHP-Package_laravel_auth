<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Support;

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

        if (true === ($features['registration'] ?? false)) {
            $resolved[] = Features::registration();
        }

        if (true === ($features['reset-passwords'] ?? false)) {
            $resolved[] = Features::resetPasswords();
        }

        if (true === ($features['email-verification'] ?? false)) {
            $resolved[] = Features::emailVerification();
        }

        if (true === ($features['update-profile-information'] ?? false)) {
            $resolved[] = Features::updateProfileInformation();
        }

        if (true === ($features['update-passwords'] ?? false)) {
            $resolved[] = Features::updatePasswords();
        }

        $twoFactor = $features['two-factor-authentication'] ?? false;

        if (true === $twoFactor) {
            $resolved[] = Features::twoFactorAuthentication();
        }

        if (is_array($twoFactor) && true === ($twoFactor['enabled'] ?? false)) {
            $resolved[] = Features::twoFactorAuthentication([
                'confirm'         => $twoFactor['confirm']          ?? true,
                'confirmPassword' => $twoFactor['confirm_password'] ?? true,
            ]);
        }

        return $resolved;
    }
}
