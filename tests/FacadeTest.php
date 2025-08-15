<?php

namespace Dovutuan\Lalog\Tests;

use Dovutuan\Lalog\Facades\LaLog;
use Dovutuan\Lalog\Services\QueryLogger;

class FacadeTest extends TestCase
{
    /** @test */
    public function it_provides_facade_access()
    {
        $this->assertInstanceOf(QueryLogger::class, LaLog::getFacadeRoot());
    }
}
