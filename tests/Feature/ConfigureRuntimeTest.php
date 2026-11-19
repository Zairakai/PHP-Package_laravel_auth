<?php

declare(strict_types=1);

namespace Zairakai\LaravelAuth\Tests\Feature;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\Attributes\Test;
use Zairakai\LaravelAuth\Tests\TestCase;

final class ConfigureRuntimeTest extends TestCase
{
    #[Test]
    public function it_leaves_the_fortify_and_sanctum_settings_of_the_application_when_asked_to(): void
    {
        self::assertSame('custom', config('fortify.guard'));
        self::assertSame(['custom.test'], config('sanctum.stateful'));
        self::assertSame('/custom-home', config('fortify.home'));
    }

    /**
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $config = $app->make(Repository::class);

        $config->set('laravel-auth.configure_runtime', false);
        $config->set('fortify.guard', 'custom');
        $config->set('fortify.home', '/custom-home');
        $config->set('sanctum.stateful', ['custom.test']);
    }
}
