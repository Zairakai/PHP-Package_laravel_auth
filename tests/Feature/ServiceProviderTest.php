<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

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
use Zairakai\LaravelAuth\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_applies_fortify_runtime_configuration(): void
    {
        $this->assertSame('web', config('fortify.guard'));
        $this->assertSame('users', config('fortify.passwords'));
        $this->assertSame('/dashboard', config('fortify.home'));
        $this->assertFalse(config('fortify.views'));
        $this->assertSame('auth', config('fortify.auth_middleware'));
        $this->assertTrue(config('fortify.lowercase_usernames'));
        $this->assertSame('/dashboard', config('fortify.redirects.login'));
        $this->assertSame('/dashboard', config('fortify.redirects.register'));
        $this->assertSame('/dashboard', config('fortify.redirects.email-verification'));
    }

    #[Test]
    public function it_applies_sanctum_runtime_configuration(): void
    {
        $this->assertSame(['web'], config('sanctum.guard'));
        $this->assertSame(
            ['localhost', 'localhost:3000', '127.0.0.1', '127.0.0.1:8000', '::1'],
            config('sanctum.stateful'),
        );
    }

    #[Test]
    public function it_registers_auth_response_bindings(): void
    {
        $this->assertInstanceOf(LoginResponse::class, $this->app->make(LoginResponseContract::class));
        $this->assertInstanceOf(LogoutResponse::class, $this->app->make(LogoutResponseContract::class));
        $this->assertInstanceOf(RegisterResponse::class, $this->app->make(RegisterResponseContract::class));
        $this->assertInstanceOf(
            PasswordResetResponse::class,
            $this->app->make(PasswordResetResponseContract::class),
        );
        $this->assertInstanceOf(
            TwoFactorLoginResponse::class,
            $this->app->make(TwoFactorLoginResponseContract::class),
        );
        $this->assertInstanceOf(
            VerifyEmailResponse::class,
            $this->app->make(VerifyEmailResponseContract::class),
        );
    }
}
