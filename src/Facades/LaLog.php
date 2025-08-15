<?php

namespace Dovutuan\Lalog\Facades;

use Illuminate\Support\Facades\Facade;

class LaLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lalog';
    }
}
