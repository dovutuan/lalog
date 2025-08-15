<?php

namespace Dovutuan\Lalog\Tests;

use Dovutuan\Lalog\Facades\LaLog;
use Dovutuan\Lalog\ServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param Application $app
     * @return string[]
     */
    public function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    public function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param Application $app
     * @return string[]
     */
    public function getPackageAliases($app)
    {
        return [
            'Lalog' => LaLog::class,
        ];
    }
}
