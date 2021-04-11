<?php

declare(strict_types=1);

namespace Sajya\Server\Binding;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\RouteBinding;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Sajya\Server\Facades\RPC;

/**
 * This is the class bound as `sajya-rpc-binder` in the Service Container and
 * this is the one that can be accessed using the {@see RPC} Facade.
 */
class BindingServiceProvider
{
    use HandlesRequestParameters;

    /**
     * The IoC container instance.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * The registered parameter binders.
     *
     * @var array
     */
    protected array $binders = [];

    /**
     * The registered binding targets.
     *
     * @var array
     */
    protected array $scopes = [];

    /**
     * The registered procedure method parameters.
     *
     * @var array
     */
    protected array $procedureMethodParams = [];

    /**
     * The registered request parameters.
     *
     * @var array
     */
    protected array $requestParameters = [];

    /**
     * Create a new instance.
     *
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Register a model binder for a request parameter.
     *
     * @param string|string[]              $requestParam         The parameter name in the RPC request to use for the
     *                                                           model binding. If the parameter is nested, use an
     *                                                           array, where each string corresponds to an attribute
     *                                                           to look into, e.g. `['post','id']` will use the `id`
     *                                                           attribute of the `post` attribute. The last or only
     *                                                           attribute may also be suffixed with a colon and the
     *                                                           field name to be used for the resolution, e.g.:
     *                                                           `user:email` or
     *                                                           `post:slug`.
     * @param string                       $class                The class name to resolve.
     * @param string|callable|mixed[]|null $scope                Optional, default: ''.
     *                                                           For details see {@see RPC::bind()}.
     * @param null|string                  $procedureMethodParam Optional, default: same as `$requestParam`.
     *                                                           For details see {@see RPC::bind()}.
     * @param null|\Closure                $failureCallback      Optional. If provided, it is called if the automatic
     *                                                           model resolution fails and can be used to perform a
     *                                                           custom resolution
     *                                                           (return an instance to be used) or error handling.
     *
     * @return void
     *
     * @see  \Illuminate\Routing\Router::model()
     * @link https://laravel.com/docs/8.x/routing#explicit-binding
     */
    public function model(
        $requestParam,
        string $class,
        $scope = '',
        $procedureMethodParam = null,
        \Closure $failureCallback = null
    ): void
    {
        $this->bind(
            $requestParam,
            RouteBinding::forModel($this->container, $class, $failureCallback),
            $scope,
            $procedureMethodParam
        );
    }

    /**
     * Register a custom binder for a request parameter.
     *
     * @param string|string[]              $requestParam         The parameter name in the RPC request to use for the
     *                                                           model binding. If the parameter is nested, use an
     *                                                           array, where each string corresponds to an attribute
     *                                                           to look into, e.g. `['post','id']` will use the `id`
     *                                                           attribute of the
     *                                                           `post` attribute.
     * @param string|callable              $binder               The callback to perform the resolution. Should return
     *                                                           the instance to be used.
     * @param string|callable|mixed[]|null $scope                Optional, default: ''.
     *                                                           This defines where the binding will be applied:
     *                                                           - Empty string: globally, for all Procedures & all
     *                                                           methods
     *                                                           - Procedure name: for all methods of the given
     *                                                           Procedure
     *                                                           - `Procedure@method`: for the given method
     *                                                           - PHP callable: for the given method
     *                                                           If array is provided, it may contain multiple strings
     *                                                           and callables, each will be applied.
     * @param null|string                  $procedureMethodParam Optional, default: same as `$requestParam` or last
     *                                                           element Provide it, if the PHP method parameter has a
     *                                                           different name than the RPC request parameter.
     *
     * @return void
     *
     * @see  \Illuminate\Routing\Router::bind()
     * @link https://laravel.com/docs/8.x/routing#customizing-the-resolution-logic
     */
    public function bind(
        $requestParam,
        $binder,
        $scope = '',
        $procedureMethodParam = null
    ): void
    {
        $key = $this->makeKey($requestParam, $scope, $procedureMethodParam);
        $this->binders[$key] = RouteBinding::forCallback($this->container, $binder);
        $this->scopes[$key] = $scope;
        if (is_null($procedureMethodParam)) {
            $procedureMethodParam = is_array($requestParam) ? end($requestParam) : $requestParam;
            $procedureMethodParam = explode(':', $procedureMethodParam)[0];
        }
        $this->procedureMethodParams[$key] = $procedureMethodParam;
        $this->requestParameters[$key] = $requestParam;
    }

    /**
     * Makes a key to be used with the arrays containing the bindings and related configuration.
     *
     * @param string|array                        $requestParam         The parameter in the RPC request to bind for.
     * @param string|callable|string[]|callable[] $scope                See the `$bind` parameter of {@see bind()}.
     * @param string|null                         $procedureMethod      The parameter of the Procedure method to bind
     *                                                                  for.
     *
     * @return string
     * @throws \JsonException
     */
    private function makeKey($requestParam, $scope, ?string $procedureMethod): string
    {
        $json = json_encode([$requestParam, $scope, $procedureMethod], JSON_THROW_ON_ERROR);

        return sha1($json);
    }

    /**
     * Resolves the bound instance for a Procedure method parameter.
     *
     * @param array           $requestParameters The parameters from the RPC request.
     * @param string          $targetParam       The name of the parameter of the Procedure method to bind for.
     * @param string|callable $targetCallable    The target Procedure method to bind for.
     *
     * @return false|mixed False if cannot resolve, the resolved instance otherwise.
     * @throws BindingResolutionException
     */
    public function resolveInstance($requestParameters, $targetParam, $targetCallable = '')
    {
        try {
            $key = $this->findKey($targetParam, $targetCallable);

            if ($key === null) {
                return false;
            }

            $requestParam = $this->requestParameters[$key];
            $value = static::resolveRequestValue($requestParameters, $requestParam);

            if ($value === null) {
                return false;
            }
            return $this->performBinding($key, $value);
        } catch (\Throwable $e) {
            throw new BindingResolutionException('Failed to perform binding resolution.', -32003, $e);
        }
    }

    /**
     * Finds the key used with the arrays for a specific Procedure method parameter.
     *
     * @param string          $targetParam    The name of the parameter of the Procedure method to bind for.
     * @param string|callable $targetCallable The target Procedure method to bind for.
     *
     * @return string|null Null if cannot be found or the key otherwise.
     * @see makeKey()
     */
    public function findKey(string $targetParam, $targetCallable = ''): ?string
    {
        foreach ($this->procedureMethodParams as $key => $boundProcedureMethodParam) {
            if ($boundProcedureMethodParam !== $targetParam) {
                continue;
            }

            $maybeBoundScope = Arr::wrap($this->scopes[$key]);

            foreach ($maybeBoundScope as $container) {
                if (self::doesCallableContain($container, $targetCallable)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Checks if a binding target scope contains an other, typically a specific method.
     *
     * @param string|callable $container
     * @param string|callable $contained
     *
     * @return bool
     */
    protected static function doesCallableContain($container, $contained)
    {
        if ('' === $contained || '' === $container) {
            return true;
        }
        // Note: php7 considers array with classname and method name callable
        // but php8 only returns true for `is_callable`, if the method is static
        if (is_callable($container)) {
            if (is_string($contained)) {
                $contained = Str::parseCallback($contained);
            }
            if (is_callable($contained)) {
                return $container === $contained;
            }
            return false;
        }
        if (is_callable($contained)) {
            $container = Str::parseCallback($container);
            return $container === $contained;
        }

        $container = static::preparescopeForComparision($container);
        $contained = static::preparescopeForComparision($contained);

        if ($container === false || $contained === false || count($container) > count($contained)) {
            return false;
        }

        foreach ($container as $index => $part) {
            if ($part !== $contained[$index]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Turns callable arrays and callable strings into arrays for comparison.
     *
     * @param string|array $scope
     *
     * @return false|string[]
     */
    private static function preparescopeForComparision($scope)
    {
        if (is_array($scope)) {
            // In php8 a "callable" array pointing at a non-static method is not
            // considered callable, but only a regular array, so we handle those
            // here
            if (count($scope) != 2) {
                return false;
            }
            $scope = implode('@', $scope);
        }
        if (!is_string($scope)) {
            return false;
        }
        // Split into comparable bits around \ and @ characters
        $scope = preg_split('/[@\\\]/', $scope);
        return $scope;
    }

    /**
     * Call the binding callback for the given key.
     *
     * @param string $key
     * @param string $value
     *
     * @return mixed The result of the binding callback.
     */
    protected function performBinding($key, $value)
    {
        return call_user_func($this->binders[$key], $value);
    }
}
