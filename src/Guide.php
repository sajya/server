<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Sajya\Server\Exceptions\MethodNotFound;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;

class Guide
{
    /**
     * Stores all available RPC commands
     *
     * @var Collection
     */
    protected Collection $map;

    /**
     * Guide constructor.
     *
     * @param string[] $procedures
     */
    public function __construct(array $procedures = [])
    {
        $this->map = collect($procedures);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function handle(string $content = ''): string
    {
        $parser = new Parser($content);

        $result = collect($parser->makeRequests())
            ->map(fn($request) => $request instanceof Request
                ? $this->handleProcedure($request, $parser->isNotification())
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
     * @param bool    $notification
     *
     * @return Response
     */
    public function handleProcedure(Request $request, bool $notification): Response
    {
        \request()->replace($request->getParams()->toArray());

        $procedure = $this->findProcedure($request);

        if ($procedure === null) {
            return $this->makeResponse(new MethodNotFound(), $request);
        }

        $result = $notification
            ? HandleProcedure::dispatchAfterResponse($procedure)
            : HandleProcedure::dispatchNow($procedure);

        return $this->makeResponse($result, $request);
    }

    /**
     * @param Request $request
     *
     * @return null|string
     */
    public function findProcedure(Request $request): ?string
    {
        $class = Str::beforeLast($request->getMethod(), '@');
        $method = Str::afterLast($request->getMethod(), '@');

        return $this->map
            ->filter(fn(string $procedure) => $this->getProcedureName($procedure) === $class)
            ->filter(fn(string $procedure) => $this->checkExistPublicMethod($procedure, $method))
            ->map(fn(string $procedure) => Str::finish($procedure, '@' . $method))
            ->first();
    }

    /**
     * @param string $procedure
     * @param string $method
     *
     * @return bool
     */
    private function checkExistPublicMethod(string $procedure, string $method): bool
    {
        try {
            return (new \ReflectionMethod($procedure, $method))->isPublic();
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $procedure
     *
     * @return string
     * @throws \ReflectionException
     */
    private function getProcedureName($procedure): ?string
    {
        try {
            $class = new ReflectionClass($procedure);
            return $class->getStaticPropertyValue('name');
        } catch (\Exception $exception) {
            return null;
        }
    }
}
