<?php

namespace Sajya\Server;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JsonRpcController
{
    /**
     * @var Guide|null
     */
    protected ?Guide $guide;

    /**
     * Invoke the controller method.
     *
     * @param Request  $request
     * @param string[] $procedures
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, array $procedures): JsonResponse
    {
        $guide = new Guide($procedures);

        $response = $guide->handle($request->getContent());

        return response()->json($response);
    }
}
