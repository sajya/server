<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JsonRpcController
{
    /**
     * @var App|null
     */
    protected ?App $guide;

    /**
     * Invoke the controller method.
     *
     * @param Request     $request
     * @param string[]    $procedures
     * @param null|string $delimiter
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, array $procedures, string $delimiter = null): JsonResponse
    {
        $guide = new App($procedures, $delimiter);

        $response = $guide->handle($request->getContent());

        return response()->json($response);
    }
}
