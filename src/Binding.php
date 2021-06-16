<?php

declare(strict_types=1);

namespace Sajya\Server;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Routing\RouteBinding;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Sajya\Server\Http\Request;

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
    public function model(string $key, string $class, Closure $callback = null)
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
    public function bind(string $key, $binder)
    {
        $this->binders[$key] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function bindResolve(Request $request): array
    {
        $possibleBindings = Arr::dot($request->getParams());

        return collect($possibleBindings)
            ->map(fn ($value, string $key) => with($value, $this->binders[$key] ?? null))
            ->mapWithKeys(function ($value, string $key) {
                $nameForArgument = (string)Str::of($key)->replace('.', '_')->camel();

                return [$nameForArgument => $value];
            })
            ->toArray();
    }
}
