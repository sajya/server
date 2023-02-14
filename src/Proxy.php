<?php

declare(strict_types=1);

namespace Sajya\Server;

use Sajya\Server\Http\Request;

interface Proxy
{
    /**
     * The method used by request handlers.
     *
     * @param \Sajya\Server\Http\Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request): mixed;
}
