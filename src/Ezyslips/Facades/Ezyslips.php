<?php

namespace ClarityTech\Ezyslips\Facades;

use Illuminate\Support\Facades\Facade;

class Ezyslips extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ezyslips';
    }
}
