<?php

declare(strict_types=1);

namespace Sajya\Server;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Routing\RouteBinding;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionParameter;

class Binding
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The registered route value binders.
     *
     * @var array
     */
    protected $binders = [];

    /**
     * Application constructor.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a model binder for a wildcard.
     */
    public function model(string $key, string $class, Closure $callback = null): void
    {
        $this->bind($key, RouteBinding::forModel($this->container, $class, $callback));
    }

    /**
     * Add a new route parameter binder.
     *
     * @param Closure|string $binder
     */
    public function bind(string $key, $binder): void
    {
        $this->binders[$key] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }

    /**
     * Binds the values of the given parameters to their corresponding type.
     *
     *
     * @throws \ReflectionException
     */
    public function bindResolve(string $procedure, Collection $params): array
    {
        $class = new ReflectionClass(Str::before($procedure, '@'));
        $method = $class->getMethod(Str::after($procedure, '@'));

        return collect($method->getParameters())
            ->map(fn (ReflectionParameter $parameter) => $parameter->getName())
            ->mapWithKeys(function (string $key) use ($params) {
                $value = Arr::get($params, $key);
                $valueDot = Arr::get($params, Str::snake($key, '.'));

                return [$key => $value ?? $valueDot];
            })
            ->map(fn ($value, string $key) => with($value, $this->binders[$key] ?? null))
            ->filter()
            ->toArray();
    }
}
