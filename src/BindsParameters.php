<?php

declare(strict_types=1);

namespace Sajya\Server;

interface BindsParameters
{
    /**
     * Maps the parameters of the RPC request to the parameters of the Procedure method.
     *
     * @return string[] Array where keys are names of the PHP method parameters,
     *                  and the values are the parameters of the RPC request to
     *                  make the instances based on.
     *                  The resolution uses the default key name of the Model, which
     *                  is 'id' by default and can be customised using the 'getRouteKeyName()'
     *                  method inside the Model class. For example ['user'=>'user_id']
     *                  will ensure '$user' parameter of the Procedure method would
     *                  receive an instance of the hinted Model type with the 'id'
     *                  matching the 'user_id' parameter in the RPC request.
     *                  The key to be used can also be customised the same way as
     *                  in Route Model Binding, e.g.: ['user'=>'address:email']
     *                  would make an instance of the hinted type of the '$user'
     *                  parameter of the Procedure method, where the email attribute
     *                  of the Model is set by the 'address' parameter in the RPC
     *                  request.
     *                  It is also possible to use nested parameters. E.g.: if the
     *                  request contains a `user` parameter, which contains an `id`
     *                  parameter, it can be mapped as: `['user'=>['user','id']]`.
     *                  It is also possible to combine the custom field and nested
     *                  parameters, e.g.: `['user'=>['user','address:email']]`.
     */
    public function getBindings(): array;
    
    /**
     * Makes the parameter to be injected into the Procedure method.
     *
     * @param string $parameterName The name of the PHP method parameter to resolve.
     *
     * @return false|null|mixed The class instance to inject or false to use default resolution.
     *                          For optional parameters, null can be returned as well.
     */
    public function resolveParameter(string $parameterName);
}
