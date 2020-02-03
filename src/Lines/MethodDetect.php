<?php

declare(strict_types=1);

namespace Sajya\Server\Lines;

use Sajya\Server\Exceptions\MethodNotFound;
use Sajya\Server\Line;
use Sajya\Server\State;

class MethodDetect extends Line
{
    /**
     * @param State $state
     *
     * @return State
     */
    public function handler(State $state): State
    {
        $request = $state->getRequest();
        $procedure = $state->getGuide()->findProcedure($request);

        return $procedure === null
            ? $state->makeResponse(new MethodNotFound())
            : $state->setProcedure($procedure);
    }
}
