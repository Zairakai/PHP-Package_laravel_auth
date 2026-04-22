<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use Zairakai\LaravelAuth\Contracts\DnsRecordChecker;
use Zairakai\LaravelAuth\Http\Responses\LoginResponse;
use Zairakai\LaravelAuth\Http\Responses\LogoutResponse;
use Zairakai\LaravelAuth\Http\Responses\PasswordResetResponse;
use Zairakai\LaravelAuth\Http\Responses\RegisterResponse;
use Zairakai\LaravelAuth\Http\Responses\TwoFactorLoginResponse;
use Zairakai\LaravelAuth\Http\Responses\VerifyEmailResponse;
use Zairakai\LaravelAuth\Repositories\BlockedEmailDomainRepository;
use Zairakai\LaravelAuth\Rules\PublicEmailDomain;
use Zairakai\LaravelAuth\Services\EmailAdmissionPolicy;
use Zairakai\LaravelAuth\Services\EmailDomainDnsChecker;
use Zairakai\LaravelAuth\Services\EmailDomainNormalizer;
use Zairakai\LaravelAuth\Services\NativeDnsRecordChecker;
use Zairakai\LaravelAuth\Support\FortifyFeatureSet;
use Zairakai\LaravelAuth\Support\StatefulDomains;

class LaravelAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-auth.php' => config_path('laravel-auth.php'),
            ], 'zairakai-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-auth.php', 'laravel-auth');

        /** @var ConfigRepository $configRepository */
        $configRepository = $this->app->make('config');

        $this->configureFortify($configRepository);
        $this->configureSanctum($configRepository);
        $this->registerEmailFilterBindings();
        $this->registerResponseBindings();
    }

    protected function configureFortify(ConfigRepository $configRepository): void
    {
        $fortifyConfig  = $configRepository->get('laravel-auth.fortify', []);
        $fortify        = is_array($fortifyConfig) ? $fortifyConfig : [];
        $redirectConfig = $configRepository->get('laravel-auth.redirects', []);
        $redirects      = is_array($redirectConfig) ? $redirectConfig : [];
        $featureConfig  = $configRepository->get('laravel-auth.features', []);

        /** @var array<string, mixed> $features */
        $features       = is_array($featureConfig) ? $featureConfig : [];

        $configRepository->set('fortify.guard', $fortify['guard'] ?? 'web');
        $configRepository->set('fortify.passwords', $fortify['passwords'] ?? 'users');
        $configRepository->set('fortify.prefix', $fortify['prefix'] ?? '');
        $configRepository->set('fortify.domain', $fortify['domain'] ?? null);
        $configRepository->set('fortify.home', $fortify['home'] ?? '/dashboard');
        $configRepository->set('fortify.views', $fortify['views'] ?? false);
        $configRepository->set('fortify.auth_middleware', $fortify['auth_middleware'] ?? 'auth');
        $configRepository->set(
            'fortify.lowercase_usernames',
            $fortify['lowercase_usernames'] ?? true,
        );
        $configRepository->set('fortify.middleware', $fortify['middleware'] ?? ['web']);
        $configRepository->set('fortify.limiters', $fortify['limiters'] ?? []);
        $configRepository->set('fortify.username', $fortify['username'] ?? 'email');
        $configRepository->set('fortify.email', $fortify['email'] ?? 'email');
        $configRepository->set('fortify.redirects', $redirects);
        $configRepository->set(
            'fortify.features',
            FortifyFeatureSet::fromConfig($features),
        );
    }

    protected function configureSanctum(ConfigRepository $configRepository): void
    {
        $sanctumConfig   = $configRepository->get('laravel-auth.sanctum', []);
        $sanctum         = is_array($sanctumConfig) ? $sanctumConfig : [];
        $statefulDomains = $sanctum['stateful'] ?? '';

        $configRepository->set(
            'sanctum.stateful',
            StatefulDomains::parse(is_string($statefulDomains) ? $statefulDomains : ''),
        );
        $configRepository->set('sanctum.guard', $sanctum['guard'] ?? ['web']);
    }

    protected function registerEmailFilterBindings(): void
    {
        $this->app->singleton(DnsRecordChecker::class, NativeDnsRecordChecker::class);
        $this->app->singleton(EmailDomainNormalizer::class);
        $this->app->singleton(EmailDomainDnsChecker::class);
        $this->app->singleton(BlockedEmailDomainRepository::class);
        $this->app->singleton(EmailAdmissionPolicy::class);
        $this->app->bind(PublicEmailDomain::class);
    }

    protected function registerResponseBindings(): void
    {
        $this->app->bind(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            LoginResponse::class,
        );
        $this->app->bind(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            LogoutResponse::class,
        );
        $this->app->bind(
            \Laravel\Fortify\Contracts\PasswordResetResponse::class,
            PasswordResetResponse::class,
        );
        $this->app->bind(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            RegisterResponse::class,
        );
        $this->app->bind(
            \Laravel\Fortify\Contracts\TwoFactorLoginResponse::class,
            TwoFactorLoginResponse::class,
        );
        $this->app->bind(
            \Laravel\Fortify\Contracts\VerifyEmailResponse::class,
            VerifyEmailResponse::class,
        );
    }
}
