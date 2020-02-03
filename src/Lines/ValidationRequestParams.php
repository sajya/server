<?php

declare(strict_types=1);

namespace Sajya\Server\Lines;

use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Line;
use Sajya\Server\Procedure;
use Sajya\Server\State;

class ValidationRequestParams extends Line
{
    /**
     * @param State $state
     *
     * @return State
     */
    public function handler(State $state): State
    {
        /** @var Procedure $procedure */
        $procedure = $state->getProcedure();

        $validation = Validator::make(
            $state->getRequest()->getParams()->toArray(),
            $procedure->rules(),
            $procedure->messages(),
            $procedure->attributes()
        );

        if ($validation->fails()) {
            return $state->makeResponse(new InvalidParams($validation->errors()->toArray()));
        }

        return $state;
    }
}
