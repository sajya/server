<?php

declare(strict_types=1);

namespace Sajya\Server\Facades;

use Illuminate\Support\Facades\Facade;
use Sajya\Server\Binding;

/**
 * Class RPC
 *
 * @method static void bind(string $key, string|callable $binder)
 * @method static void model(string $key, string $class, \Closure|null $callback = null)
 *
 * @see \Sajya\Server\Binding
 */
class RPC extends Facade
{
    /**
     * Initiate a mock expectation on the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Binding::class;
    }
}
