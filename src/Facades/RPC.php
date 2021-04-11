<?php

declare(strict_types=1);

namespace Sajya\Server\Facades;

use Illuminate\Support\Facades\Facade;
use Sajya\Server\Binding\BindingServiceProvider;

/**
 * @mixin BindingServiceProvider
 */
class RPC extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BindingServiceProvider::class;
    }
}
