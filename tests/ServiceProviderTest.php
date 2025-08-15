<?php

namespace Dovutuan\Lalog\Tests;

use Dovutuan\Lalog\ServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_the_service_provider()
    {
        $provider = new ServiceProvider($this->app);
        $provider->register();

        $this->assertTrue($this->app->bound('lalog'));
    }

    /** @test */
    public function it_publishes_configuration()
    {
        $provider = new ServiceProvider($this->app);

        // Get the protected publishes array using reflection
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('publishes');
        $method->setAccessible(true);

        $publishes = $method->invoke($provider);

        $this->assertArrayHasKey('lalog', $publishes);
        $this->assertEquals(__DIR__ . '/../src/Config/lalog.php', realpath($publishes['lalog'][0]));
    }

    /** @test
     * @throws BindingResolutionException
     */
    public function it_registers_logger_when_enabled()
    {
        Config::set('lalog.enabled', true);

        $mock = $this->createMock(\Dovutuan\Lalog\Services\QueryLogger::class);
        $mock->expects($this->once())
            ->method('register');

        $this->app->instance('lalog', $mock);

        $provider = new ServiceProvider($this->app);
        $provider->boot();
    }
}
