<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Unit;

use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Support\FortifyFeatureSet;
use Zairakai\LaravelAuth\Tests\TestCase;

final class FortifyFeatureSetTest extends TestCase
{
    #[Test]
    public function it_ignores_disabled_features(): void
    {
        $features = FortifyFeatureSet::fromConfig([
            'registration'               => false,
            'reset-passwords'            => false,
            'email-verification'         => false,
            'update-profile-information' => false,
            'update-passwords'           => false,
            'two-factor-authentication'  => false,
        ]);

        $this->assertSame([], $features);
    }

    #[Test]
    public function it_resolves_enabled_features(): void
    {
        $features = FortifyFeatureSet::fromConfig([
            'registration'               => true,
            'reset-passwords'            => true,
            'email-verification'         => true,
            'update-profile-information' => true,
            'update-passwords'           => true,
            'two-factor-authentication'  => [
                'enabled'          => true,
                'confirm'          => false,
                'confirm_password' => true,
            ],
        ]);

        $this->assertSame([
            Features::registration(),
            Features::resetPasswords(),
            Features::emailVerification(),
            Features::updateProfileInformation(),
            Features::updatePasswords(),
            Features::twoFactorAuthentication([
                'confirm'         => false,
                'confirmPassword' => true,
            ]),
        ], $features);
    }
}
