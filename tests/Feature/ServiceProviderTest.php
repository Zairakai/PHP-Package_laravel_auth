<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Http\Responses\LoginResponse;
use Zairakai\LaravelAuth\Http\Responses\LogoutResponse;
use Zairakai\LaravelAuth\Http\Responses\PasswordResetResponse;
use Zairakai\LaravelAuth\Http\Responses\RegisterResponse;
use Zairakai\LaravelAuth\Http\Responses\TwoFactorLoginResponse;
use Zairakai\LaravelAuth\Http\Responses\VerifyEmailResponse;
use Zairakai\LaravelAuth\LaravelAuthServiceProvider;
use Zairakai\LaravelAuth\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_applies_fortify_runtime_configuration(): void
    {
        self::assertSame('web', config('fortify.guard'));
        self::assertSame('users', config('fortify.passwords'));
        self::assertSame('/dashboard', config('fortify.home'));
        self::assertFalse(config('fortify.views'));
        self::assertSame('auth', config('fortify.auth_middleware'));
        self::assertTrue(config('fortify.lowercase_usernames'));
        self::assertSame('/dashboard', config('fortify.redirects.login'));
        self::assertSame('/dashboard', config('fortify.redirects.register'));
        self::assertSame('/dashboard', config('fortify.redirects.email-verification'));
    }

    #[Test]
    public function it_applies_sanctum_runtime_configuration(): void
    {
        self::assertSame(['web'], config('sanctum.guard'));
        self::assertSame(
            ['localhost', 'localhost:3000', '127.0.0.1', '127.0.0.1:8000', '::1'],
            config('sanctum.stateful'),
        );
    }

    #[Test]
    public function it_loads_the_translations_of_the_indicators(): void
    {
        self::assertSame('Phishing', trans('laravel-auth::indicators.phishing'));
    }

    #[Test]
    public function it_publishes_the_config_the_migration_and_the_translations(): void
    {
        foreach (['zairakai-config', 'zairakai-migrations', 'zairakai-translations'] as $tag) {
            self::assertNotSame([], ServiceProvider::pathsToPublish(LaravelAuthServiceProvider::class, $tag), $tag);
        }
    }

    #[Test]
    public function it_registers_auth_response_bindings(): void
    {
        self::assertInstanceOf(LoginResponse::class, resolve(LoginResponseContract::class));
        self::assertInstanceOf(LogoutResponse::class, resolve(LogoutResponseContract::class));
        self::assertInstanceOf(RegisterResponse::class, resolve(RegisterResponseContract::class));
        self::assertInstanceOf(
            PasswordResetResponse::class,
            resolve(PasswordResetResponseContract::class),
        );
        self::assertInstanceOf(
            TwoFactorLoginResponse::class,
            resolve(TwoFactorLoginResponseContract::class),
        );
        self::assertInstanceOf(
            VerifyEmailResponse::class,
            resolve(VerifyEmailResponseContract::class),
        );
    }
}
