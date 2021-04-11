<?php

declare(strict_types=1);

namespace Sajya\Server\Facades;

use Sajya\Server\Binding\BindingServiceProvider;
use Illuminate\Support\Facades\Facade;

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
