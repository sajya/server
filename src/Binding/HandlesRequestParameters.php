<?php

declare(strict_types=1);

namespace Sajya\Server\Binding;

trait HandlesRequestParameters
{
    /**
     * Gets the value of a Request parameter.
     *
     * @param array           $requestParameters The parameters from the RPC request.
     * @param string|string[] $requestParam      A pointer for a parameter in the RPC request.
     *                                           If the parameter is nested, use an array, where each string
     *                                           corresponds to an attribute to look into,
     *                                           e.g. `['post','id']` will use the `id`  attribute of the
     *                                           `post` attribute.
     *
     * @return null|mixed The value of the parameter or null if not found.
     */
    protected static function resolveRequestValue(array $requestParameters, $requestParam)
    {
        if (! is_array($requestParam)) {
            return $requestParameters[$requestParam] ?? null;
        }
        $value = $requestParameters;
        foreach ($requestParam as $param) {
            if (! isset($value[$param])) {
                return null;
            }
            $value = $value[$param];
        }

        return $value;
    }
}
