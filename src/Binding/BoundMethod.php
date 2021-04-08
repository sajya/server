<?php

declare(strict_types=1);

namespace Sajya\Server\Binding;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Reflector;

class BoundMethod extends \Illuminate\Container\BoundMethod
{
    use HandlesRequestParameters;
    
    /**
     * Get the dependency for the given call parameter.
     *
     * @param Container $container
     * @param \ReflectionParameter            $parameter
     * @param array                           $parameters
     * @param array                           $dependencies
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected static function addDependencyForCallParameter($container, $parameter, array &$parameters, &$dependencies)
    {
        // Attempt custom binding resolution
        foreach ($dependencies as $dependency) {
            if (is_object($dependency) && $dependency instanceof BindsParameters) {
                if (($maybeDependency = $dependency->resolveParameter($parameter->getName())) !== false) {
                    if (is_null($maybeDependency)) {
                        if ($parameter->isOptional()) {
                            $dependencies[] = $maybeDependency;
                            return;
                        } else {
                            throw new BindingResolutionException('Custom resolution logic returned `null`, but parameter is not optional.', -32000);
                        }
                    }
                    
                    $paramType = Reflector::getParameterClassName($parameter);
                    if ($maybeDependency instanceof $paramType) {
                        $dependencies[] = $maybeDependency;
                        return;
                    } else {
                        throw new BindingResolutionException('Custom resolution logic returned a parameter with an invalid type.', -32001);
                    }
                }
            }
        }
        
        // Attempt resolution based on parameter mapping
        $paramName = $parameter->getName();
        foreach ($dependencies as $dependency) {
            if (is_object($dependency) && $dependency instanceof BindsParameters) {
                $parameterMap = $dependency->getBindings();
                if (isset($parameterMap[$paramName])) {
                    $instance = $container->make(Reflector::getParameterClassName($parameter));
                    if (!$instance instanceof UrlRoutable) {
                        throw new BindingResolutionException('Mapped parameter type must implement `UrlRoutable` interface.', -32002);
                    }
                    [ $instanceValue, $instanceField ] = self::getValueAndFieldByMapEntry($parameterMap[$paramName]);
                    if (!$model = $instance->resolveRouteBinding($instanceValue, $instanceField)) {
                        throw (new ModelNotFoundException('', -32003))->setModel(get_class($instance), [$instanceValue]);
                    }
                    $dependencies[] = $model;
                    return;
                }
            }
        }
        
        // Attempt resolution using the Global bindings
        /** @var BindingServiceProvider $binder */
        $binder = $container->make('sajya-rpc-binder');
        $procedureClass = $parameter->getDeclaringClass()->name.'@'.$parameter->getDeclaringFunction()->name;
        $requestParameters = request()->request->all();
        
        $maybeInstance = $binder->resolveInstance(
            $requestParameters,
            $paramName,
            $procedureClass
        );
        if (false!==$maybeInstance) {
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
            $requestParamMapEntry[count($requestParamMapEntry)-1] = $entry[0];
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
