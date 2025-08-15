<?php

namespace Dovutuan\Lalog\Tests;

use Dovutuan\Lalog\Services\QueryLogger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionMethod;
use ReflectionProperty;

class QueryLoggerTest extends TestCase
{
    public QueryLogger $queryLogger;
    public string $testDirectory = 'test-query-logs';

    public function setUp(): void
    {
        parent::setUp();

        // Configure test environment
        Config::set('lalog.enabled', true);
        Config::set('lalog.disk', 'local');
        Config::set('lalog.storage.directory_path', $this->testDirectory);
        Config::set('lalog.storage.max_file_size_bytes', 1024); // 1KB for testing
        Config::set('lalog.formatting.log_file_date_format', 'Y-m-d');
        Config::set('lalog.formatting.query_timestamp_format', 'Y-m-d H:i:s');

        $this->queryLogger = new QueryLogger();

        // Clear test directory
        Storage::fake('local');
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_initializes_configuration_correctly()
    {
        $this->assertEquals($this->testDirectory, $this->getPrivateProperty('logDirectoryPath'));
        $this->assertEquals('local', $this->getPrivateProperty('filesystemDisk'));
        $this->assertEquals(1024, $this->getPrivateProperty('maxLogFileSize'));
    }

    /** @test */
    public function it_creates_new_log_file_when_registering()
    {
        $this->queryLogger->register();

        $files = Storage::allFiles($this->testDirectory);
        $this->assertCount(1, $files);
        $this->assertStringContainsString('sql-' . date('Y-m-d'), $files[0]);
    }

    /** @test */
    public function it_creates_new_file_when_max_size_is_exceeded()
    {
        // Force small max size for testing
        Config::set('lalog.storage.max_file_size_bytes', 10);

        $this->callPrivateMethod('prepareLogFile');
        $initialFile = $this->getPrivateProperty('currentLogFilename');

        // Simulate file reaching max size
        Storage::put($initialFile, str_repeat('x', 20));

        // Prepare again - should create new file
        $this->callPrivateMethod('prepareLogFile');
        $newFile = $this->getPrivateProperty('currentLogFilename');

        $this->assertNotEquals($initialFile, $newFile);
        $this->assertStringContainsString('-1.sql', $newFile);
    }

    /** @test */
    public function it_registers_query_listener()
    {
        $mock = Mockery::mock(DB::getFacadeRoot());
        DB::swap($mock);

        $mock->shouldReceive('listen')
            ->once()
            ->with(Mockery::on(function ($callback) {
                return is_callable($callback);
            }));

        $this->callPrivateMethod('registerQueryListener');
    }

    /** @test */
    public function it_records_queries_correctly()
    {
        $this->callPrivateMethod('prepareLogFile');

        $query = (object)[
            'sql' => 'SELECT * FROM users WHERE id = ?',
            'bindings' => [1],
            'time' => 5.2,
        ];

        $this->queryLogger->recordQuery($query);

        $content = Storage::get($this->getPrivateProperty('currentLogFilename'));

        $this->assertStringContainsString('QUERY LOG START', $content);
        $this->assertStringContainsString('SELECT * FROM users WHERE id = \'1\'', $content);
        $this->assertStringContainsString('Duration: 5.2 ms', $content);
        $this->assertStringContainsString('QUERY LOG END', $content);
    }

    /** @test */
    public function it_formats_date_bindings_correctly()
    {
        $this->callPrivateMethod('prepareLogFile');

        $date = new \DateTime('2023-01-01');
        $query = (object)[
            'sql' => 'SELECT * FROM users WHERE created_at > ?',
            'bindings' => [$date],
            'time' => 1.0,
        ];

        $this->queryLogger->recordQuery($query);

        $content = Storage::get($this->getPrivateProperty('currentLogFilename'));
        $expectedDate = $date->format('Y-m-d H:i:s');

        $this->assertStringContainsString("created_at > '$expectedDate'", $content);
    }

    /** @test */
    public function it_does_not_log_when_disabled()
    {
        Config::set('lalog.enabled', false);

        $provider = new \Dovutuan\Lalog\ServiceProvider($this->app);
        $provider->boot();

        $this->assertFalse($this->app->bound('lalog'));
    }

    /**
     * Helper method to call private methods
     */
    public function callPrivateMethod(string $methodName, array $parameters = [])
    {
        $reflectionMethod = new ReflectionMethod(QueryLogger::class, $methodName);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($this->queryLogger, $parameters);
    }

    /**
     * Helper method to get private properties
     */
    public function getPrivateProperty(string $propertyName)
    {
        $reflectionProperty = new ReflectionProperty(QueryLogger::class, $propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($this->queryLogger);
    }
}
