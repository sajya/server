<?php

namespace Sajya\Server;

use Sajya\Server\Exceptions\MethodNotFound;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;

class JsonRpcController
{
    /**
     * @var Guide
     */
    protected ?Guide $guide;

    /**
     * @return mixed
     */
    public function handle()
    {
        $parser = new Parser((string)\request()->getContent());

        $result = collect($parser->makeRequests())
            ->map(fn($request) => $request instanceof Request
                ? $this->handleProcedure($request)
                : $this->makeResponse($request)
            );

        $response = $parser->isBatch() ? $result->all() : $result->first();

        return json_encode($response, JSON_THROW_ON_ERROR, 512);
    }


    /**
     * Invoke the controller method.
     *
     * @param array $procedures
     *
     * @return mixed
     */
    public function __invoke(array $procedures)
    {
        $this->guide = new Guide($procedures);

        return $this->handle();
    }


    /**
     * @param Request $request
     * @param mixed   $result
     *
     * @return Response
     */
    public function makeResponse($result = null, Request $request = null): Response
    {
        $request ??= new Request();

        return tap(new Response(), function (Response $response) use ($request, $result) {
            $response->setId($request->getId());
            $response->setVersion($request->getVersion());
            $response->setResult($result);
        });
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handleProcedure(Request $request): Response
    {
        \request()->replace($request->getParams()->toArray());

        $procedure = $this->guide->findProcedure($request);

        if ($procedure === null) {
            return $this->makeResponse(new MethodNotFound(), $request);
        }

        $result = HandleProcedure::dispatchNow($procedure);

        return $this->makeResponse($result, $request);
    }
}
