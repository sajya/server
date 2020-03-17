<?php

namespace Sajya\Server;

use Illuminate\Http\Request;

class JsonRpcController
{
    /**
     * @var Guide
     */
    protected ?Guide $guide;

    /**
     * Invoke the controller method.
     *
     * @param Request $request
     * @param array   $procedures
     *
     * @return mixed
     */
    public function __invoke(Request $request, array $procedures)
    {
        $guide = new Guide($procedures);

        return $guide->handle($request->getContent());
    }
}
