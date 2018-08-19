<?php

namespace Rennokki\Chargeswarm\Test;

use Rennokki\Plans\Test\Models\User;
use Rennokki\Chargeswarm\Models\Invoice;
use Rennokki\Chargeswarm\Models\Subscription;
use Orchestra\Testbench\TestCase as Orchestra;
use Rennokki\Chargeswarm\Models\SubscriptionUsage;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->resetDatabase();

        $this->loadLaravelMigrations(['--database' => 'sqlite']);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->withFactories(__DIR__.'/../database/factories');
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Rennokki\Chargeswarm\ChargeswarmServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
        $app['config']->set('chargeswarm.models.subscription', Subscription::class);
        $app['config']->set('chargeswarm.models.subscriptionUsage', SubscriptionUsage::class);
        $app['config']->set('chargeswarm.models.invoice', Invoice::class);
    }

    protected function resetDatabase()
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }
}
