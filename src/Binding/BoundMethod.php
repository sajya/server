<?php

declare(strict_types=1);

namespace Sajya\Server\Binding;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Reflector;
use Sajya\Server\Exceptions\BindingResolutionException;

class BoundMethod extends \Illuminate\Container\BoundMethod
{
    use HandlesRequestParameters;

    /**
     * Get the dependency for the given call parameter.
     *
     * @param Container            $container
     * @param \ReflectionParameter $parameter
     * @param array                $parameters
     * @param array                $dependencies
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    protected static function addDependencyForCallParameter($container, $parameter, array &$parameters, &$dependencies)
    {
        // Attempt custom binding resolution
        collect($dependencies)
            ->whereInstanceOf(BindsParameters::class)
            ->map(fn ($dependency)    => $dependency->resolveParameter($parameter->getName()))
            ->filter(fn ($dependency) => $dependency !== false)
            ->each(function ($dependency) use ($parameter) {
                throw_if(is_null($dependency) && ! $parameter->isOptional(), BindingResolutionException::class);
            })
            ->each(function ($dependency) use ($parameter, &$dependencies) {
                if (is_null($dependency) && $parameter->isOptional()) {
                    $dependencies[] = $dependency;

                    return;
                }

                $paramType = Reflector::getParameterClassName($parameter);
                if ($dependency instanceof $paramType) {
                    $dependencies[] = $dependency;

                    return;
                }

                throw new BindingResolutionException([
                    'title' => 'Custom resolution logic returned a parameter with an invalid type.',
                    'code'  => -32001,
                ]);
            });


        // Attempt resolution based on parameter mapping
        $paramName = $parameter->getName();
        foreach ($dependencies as $dependency) {
            if (!is_object($dependency) || !$dependency instanceof BindsParameters) {
                continue;
            }

            $parameterMap = $dependency->getBindings();
            if (!isset($parameterMap[$paramName])) {
                continue;
            }

            $instance = $container->make(Reflector::getParameterClassName($parameter));
            if (! $instance instanceof UrlRoutable) {
                throw new BindingResolutionException([
                    'message' => 'Mapped parameter type must implement `UrlRoutable` interface.',
                    'code'    => -32002,
                ]);
            }

            [$instanceValue, $instanceField] = self::getValueAndFieldByMapEntry($parameterMap[$paramName]);
            if (! $model = $instance->resolveRouteBinding($instanceValue, $instanceField)) {
                throw (new ModelNotFoundException('', -32003))->setModel(get_class($instance), [$instanceValue]);
            }
            $dependencies[] = $model;

            return;
        }

        // Attempt resolution using the Global bindings
        /** @var BindingServiceProvider $binder */
        $binder = $container->make(BindingServiceProvider::class);
        $procedureClass = $parameter->getDeclaringClass()->name . '@' . $parameter->getDeclaringFunction()->name;
        $requestParameters = request()->request->all();

        $maybeInstance = $binder->resolveInstance(
            $requestParameters,
            $paramName,
            $procedureClass
        );
        if (false !== $maybeInstance) {
            $dependencies[] = $maybeInstance;

            return;
        }

        parent::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
    }

    /**
     * Determines the value and the field to be used for model lookup based on the current request.
     *
     * @param array|string $requestParamMapEntry
     *
     * @return array
     */
    private static function getValueAndFieldByMapEntry($requestParamMapEntry)
    {
        if (is_array($requestParamMapEntry)) {
            $last = end($requestParamMapEntry);
            $entry = explode(':', $last);
            $requestParamMapEntry[count($requestParamMapEntry) - 1] = $entry[0];
        } elseif (is_string($requestParamMapEntry)) {
            $entry = explode(':', $requestParamMapEntry);
            $requestParamMapEntry = $entry[0];
        } else {
            throw new \LogicException('$requestParamMapEntry must be an array or string.');
        }
        $value = self::resolveRequestValue(request()->request->all(), $requestParamMapEntry);

        return [$value, $entry[1] ?? null];
    }
}
