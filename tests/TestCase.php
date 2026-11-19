<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
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

    /**
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $config = $app->make(Repository::class);

        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $config->set('cache.default', 'array');
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
