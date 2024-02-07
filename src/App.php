<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Sajya\Server\Exceptions\MethodNotFound;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;

class App
{
    /**
     * Default delimiter for method name.
     */
    protected const DEFAULT_DELIMITER = '@';

    /**
     * Stores all available RPC commands.
     *
     * @var Collection
     */
    protected Collection $map;

    /**
     * Stores delimiter
     *
     * @var string
     */
    protected string $delimiter;

    /**
     * App constructor.
     *
     * @param string[]    $procedures An array of procedures to register with the App.
     * @param null|string $delimiter  The delimiter to use for separating procedure names from method names.
     */
    public function __construct(array $procedures = [], ?string $delimiter = self::DEFAULT_DELIMITER)
    {
        $this->map = collect($procedures)
            ->each(fn (string $class) => abort_unless(
                is_subclass_of($class, Procedure::class),
                500,
                "Class '$class' must extends ".Procedure::class
            ));
        $this->delimiter = $delimiter ?? self::DEFAULT_DELIMITER;
    }

    /**
     * Terminate the application and return the JSON-RPC response.
     *
     * @param string $content
     *
     * @return Response[]|Response|null
     */
    public function terminate(string $content = '')
    {
        return tap($this->handle($content), fn () => Application::getInstance()->terminate());
    }

    /**
     * Handles a JSON-RPC request or batch of requests.
     *
     * @param string $content
     *
     * @return Response[]|Response|null
     */
    public function handle(string $content = '')
    {
        $parser = new Parser($content);

        $result = collect($parser->makeRequests())
            ->map(
                fn ($request) => $request instanceof Request
                    ? $this->handleProcedure($request, $request->isNotification())
                    : $this->makeResponse($request)
            )
            ->reject(fn (Response $response) => $response->isNotification())
            ->values();

        return $parser->isBatch()
            ? $result->all()
            : $result->first();
    }

    /**
     * Search for a procedure and execute it.
     *
     * @param Request $request
     * @param bool    $notification
     *
     * @return Response
     */
    public function handleProcedure(Request $request, bool $notification): Response
    {
        request()->replace($request->getParams()->toArray());
        app()->bind(Request::class, fn () => $request);

        $procedure = $this->findProcedure($request);

        if ($procedure === null) {
            return $this->makeResponse(new MethodNotFound(), $request);
        }

        $result = $notification
            ? HandleProcedure::dispatchAfterResponse($procedure, $request)
            : (new HandleProcedure($procedure, $request))->handle();

        return $this->makeResponse($result, $request);
    }

    /**
     * Find procedure by request.
     *
     * @param Request $request
     *
     * @return null|string
     */
    public function findProcedure(Request $request): ?string
    {
        $class = Str::beforeLast($request->getMethod(), $this->delimiter);
        $method = Str::afterLast($request->getMethod(), $this->delimiter);

        return $this->map
            ->filter(fn (string $procedure) => $this->getProcedureName($procedure) === $class)
            ->filter(fn (string $procedure) => $this->checkExistPublicMethod($procedure, $method))
            ->map(fn (string $procedure) => Str::finish($procedure, self::DEFAULT_DELIMITER.$method))
            ->whenEmpty(fn (Collection $collection) => $collection->push($this->findProxy($class)))
            ->first();
    }

    /**
     * Get the name of the procedure for the given class.
     *
     * @param string $procedure
     *
     * @return string
     */
    private function getProcedureName(string $procedure): string
    {
        return (new ReflectionClass($procedure))->getStaticPropertyValue('name');
    }

    /**
     * Check if the given procedure has a public method with the given name.
     *
     * @param string $procedure
     * @param string $method
     *
     * @return bool
     */
    private function checkExistPublicMethod(string $procedure, string $method): bool
    {
        return method_exists($procedure, $method) && (new ReflectionMethod($procedure, $method))->isPublic();
    }

    /**
     * Fallback to the first procedure that implements the Proxy interface.
     *
     * @param string $class
     *
     * @return null|string
     */
    public function findProxy(string $class): ?string
    {
        return $this->map
            ->filter(fn (string $procedure) => $this->getProcedureName($procedure) === $class)
            ->filter(fn (string $procedure) => is_subclass_of($procedure, Proxy::class))
            ->map(fn (string $procedure) => Str::finish($procedure, self::DEFAULT_DELIMITER.'__invoke'))
            ->first();
    }

    /**
     * Create a Response object from the given result and Request object.
     *
     * @param mixed|null   $result
     * @param Request|null $request
     *
     * @return Response
     */
    public function makeResponse($result = null, ?Request $request = null): Response
    {
        return Response::makeFromResult($result, $request);
    }
}
