<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;
use Sajya\Server\Lines\MethodDetect;
use Sajya\Server\Lines\UsefulWork;
use Sajya\Server\Lines\ValidationRequestFormat;
use Sajya\Server\Lines\ValidationRequestParams;

class Guide
{
    /**
     * Stores all available RPC commands
     *
     * @var Collection
     */
    protected $map;

    /**
     * Guide constructor.
     *
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = collect($map);
    }

    /**
     * @param string|null $content
     *
     * @return string
     */
    public function handle(string $content = null): string
    {
        $parser = new Parser($content);
        $rpcRequests = $parser->makeRequests();

        $result = collect($rpcRequests)
            ->map(fn(Request $request) => $this->handleProcedure($request));

        $response = $parser->isBatch() ? $result->all() : $result->first();

        return json_encode($response, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handleProcedure(Request $request): Response
    {
        return app(Pipeline::class)
            ->send(new State($this, $request))
            ->through([
                MethodDetect::class,
                ValidationRequestFormat::class,
                ValidationRequestParams::class,
                UsefulWork::class,
            ])
            ->via('run')
            ->then(fn(State $state) => $state->getResponse());
    }

    /**
     * @param Request $request
     *
     * @return null|Procedure
     */
    public function findProcedure(Request $request): ?Procedure
    {
        return $this->map
            ->map(fn($procedure) => !is_object($procedure) ? app()->make($procedure) : $procedure)
            ->filter(fn(Procedure $procedure) => $procedure::$name === $request->getMethod())
            ->first();
    }

    /**
     * @param IlluminateRequest $request
     *
     * @return JsonResponse
     */
    public function __invoke(IlluminateRequest $request): JsonResponse
    {
        return response()->json(
            $this->handle($request->getContent())
        );
    }
}
