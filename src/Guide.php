<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sajya\Server\Http\Request;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
     * @param string|null|array $path
     */
    public function __construct($path = null)
    {
        $this->map = collect();
        $path ??= app_path();

        if (is_array($path)) {
            $this->map = $this->map->merge($path);
            return;
        }

        $this->map = $this->findProceduresClass($path);
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
    private function findProceduresClass(string $path = 'Http/Procedures'): Collection
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
}
