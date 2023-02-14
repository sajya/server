<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Contracts\Routing\UrlRoutable;

class FixtureBind implements UrlRoutable, \Stringable
{
    /**
     * @var string
     */
    protected string $resolveBind;

    /**
     * @param string $resolveBind
     */
    public function __construct(string $resolveBind = '')
    {
        $this->resolveBind = $resolveBind;
    }

    public function getRouteKey()
    {
        // TODO: Implement getRouteKey() method.
    }

    public function getRouteKeyName()
    {
        // TODO: Implement getRouteKeyName() method.
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return new FixtureBind('bind-'.$value);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->resolveBind;
    }
}
