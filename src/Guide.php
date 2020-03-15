<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Sajya\Server\Http\Request;

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
     * @param array $procedures
     */
    public function __construct(array $procedures = [])
    {
        $this->map = collect($procedures);
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
