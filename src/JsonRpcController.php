<?php


namespace Sajya\Server;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Sajya\Server\Exceptions\MethodNotFound;
use Sajya\Server\Exceptions\ParseErrorException;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;
use Sajya\Server\Tests\Fixtures\AbortProcedure;
use Sajya\Server\Tests\Fixtures\AlwaysResultProcedure;
use Sajya\Server\Tests\Fixtures\DependencyInjectionProcedure;
use Sajya\Server\Tests\Fixtures\SubtractProcedure;
use Sajya\Server\Tests\Fixtures\SumProcedure;

class JsonRpcController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return false|string
     */
    public function handle(\Illuminate\Http\Request $request)
    {
        $parser = new Parser($request->getContent());

        if ($parser->isError()) {
            return $this->makeResponse(new ParseErrorException());
        }

        $rpcRequests = collect($parser->makeRequests());

        $result = $rpcRequests
            ->map(fn($request) => $request instanceof Request
                ? $this->handleProcedure($request)
                : $this->makeResponse($request)
            );

        $response = $parser->isBatch() ? $result->all() : $result->first();

        return json_encode($response, JSON_THROW_ON_ERROR, 512);
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
        $guide = new Guide([
            SubtractProcedure::class,
            DependencyInjectionProcedure::class,
            SumProcedure::class,
            AbortProcedure::class,
            AlwaysResultProcedure::class,
        ]);

        \request()->merge($request->getParams()->toArray());

        $procedure = $guide->findProcedure($request);

        if ($procedure === null) {
            return $this->makeResponse(new MethodNotFound(), $request);
        }


        $result = HandleProcedure::dispatchNow($procedure);

        return $this->makeResponse($result, $request);
    }

}
