<?php

declare(strict_types=1);

namespace Sajya\Server\Facades;

use Sajya\Server\Binding\BindingServiceProvider;

/**
 * @mixin BindingServiceProvider
 */
class RPC extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sajya-rpc-binder';
    }
}
