<?php

namespace Russsiq\GRecaptcha\Support\Facades;

use Illuminate\Support\Facades\Facade;

class GRecaptcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'g_recaptcha';
    }
}
