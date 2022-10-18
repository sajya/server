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
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a model binder for a wildcard.
     *
     * @param string        $key
     * @param string        $class
     * @param \Closure|null $callback
     *
     * @return void
     */
    public function model(string $key, string $class, Closure $callback = null): void
    {
        $this->bind($key, RouteBinding::forModel($this->container, $class, $callback));
    }

    /**
     * Add a new route parameter binder.
     *
     * @param string         $key
     * @param Closure|string $binder
     *
     * @return void
     */
    public function bind(string $key, $binder): void
    {
        $this->binders[$key] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }

    /**
     * @param string                         $procedure
     * @param \Illuminate\Support\Collection $params
     *
     * @throws \ReflectionException
     *
     * @return array
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
