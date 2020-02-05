<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        return HandleProcedure::dispatchNow($this, $request);
    }

    /**
     * @param Request $request
     *
     * @return null|Procedure
     */
    public function findProcedure(Request $request): ?Procedure
    {
        return $this->map
            ->whenEmpty(fn(Collection $collection) => $collection->merge($this->findProceduresClass()))
            ->map(fn($procedure) => !is_object($procedure) ? app()->make($procedure) : $procedure)
            ->filter(fn(Procedure $procedure) => $procedure::$name === $request->getMethod())
            ->first();
    }


    /**
     * @param string $path
     *
     * @return Collection
     */
    public function findProceduresClass(string $path = 'Http/Procedures'): Collection
    {
        $namespace = app()->getNamespace();
        $directory = app_path($path);

        if (!is_dir($directory)) {
            return collect();
        }

        $files = (new Finder())->in($directory)->files();

        return collect($files)
            ->map(fn(SplFileInfo $resource) => $this->createClassForResource($resource, $namespace))
            ->filter(fn(SplFileInfo $resource) => is_subclass_of($resource->getPathname(), Procedure::class));
    }

    /**
     * @param SplFileInfo $resource
     * @param string      $namespace
     *
     * @return string
     */
    private function createClassForResource(SplFileInfo $resource, string $namespace): string
    {
        return $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($resource->getPathname(), app_path() . DIRECTORY_SEPARATOR)
            );
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
