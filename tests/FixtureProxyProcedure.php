<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Attributes\Procedure;
use Sajya\Server\Http\Request;
use Sajya\Server\Proxy;

#[Procedure('proxy')]
class FixtureProxyProcedure implements Proxy
{
    /**
     * The method that will be called if there is no match.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        return $request->getParams();
    }
}
