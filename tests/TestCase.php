<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests;

use Illuminate\Support\Facades\Artisan;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Zairakai\LaravelAuth\LaravelAuthServiceProvider;
use Zairakai\LaravelEssentials\EssentialsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', ['--database' => 'testing']);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('cache.default', 'array');
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FortifyServiceProvider::class,
            SanctumServiceProvider::class,
            EssentialsServiceProvider::class,
            LaravelAuthServiceProvider::class,
        ];
    }
}
