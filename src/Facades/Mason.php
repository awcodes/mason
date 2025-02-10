<?php

namespace Awcodes\Mason\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Awcodes\Mason\Mason
 */
class Mason extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Awcodes\Mason\Mason::class;
    }
}
