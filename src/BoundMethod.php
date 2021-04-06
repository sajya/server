<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Reflector;

/**
 * Class BoundMethod
 */
class BoundMethod extends \Illuminate\Container\BoundMethod
{
    /**
     * Get the dependency for the given call parameter.
     *
     * @param \Illuminate\Container\Container $container
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
        foreach ($dependencies as $dependency) {
            if (is_object($dependency) && $dependency instanceof BindsParameters) {
                $parameterMap = $dependency->getBindings();
                $paramName = $parameter->getName();
                if (isset($parameterMap[$paramName])) {
                    $instance = $container->make(Reflector::getParameterClassName($parameter));
                    if (!$instance instanceof UrlRoutable) {
                        throw new BindingResolutionException('Mapped parameter type must implement `UrlRoutable` interface.', -32002);
                    }
                    [ $instanceValue, $instanceField ] = self::getValueAndFieldFromMapEntry($parameterMap[$paramName]);
                    if (!$model = $instance->resolveRouteBinding($instanceValue, $instanceField)) {
                        throw (new ModelNotFoundException('', -32003))->setModel(get_class($instance), [$instanceValue]);
                    }
                    $dependencies[] = $model;
                    return;
                }
            }
        }
        
        parent::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies);
    }
    
    private static function getValueAndFieldFromMapEntry($mapEntry)
    {
        $entry = explode(':', $mapEntry);
        return [request($entry[0]), $entry[1] ?? null];
    }
}
