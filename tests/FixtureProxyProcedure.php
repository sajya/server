<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;
use Sajya\Server\Proxy;

class FixtureProxyProcedure extends Procedure implements Proxy
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     *
     * @var string
     */
    public static string $name = 'proxy';

    /**
     * @param \Sajya\Server\Http\Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request): mixed
    {
        return $request->getParams()->toArray();
    }
}
