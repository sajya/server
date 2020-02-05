<?php

declare(strict_types=1);

namespace Sajya\Server;

use Closure;

abstract class Line
{
    /**
     * Before each start, the presence of defects is checked.
     *
     * @param State   $state
     * @param Closure $next
     *
     * @return object
     */
    public function run(State $state, Closure $next): object
    {
        $isError = optional($state->getResponse())->isError();

        if ($isError) {
            return $next($state);
        }

        return $next($this->handler($state));
    }

    /**
     * @param State $state
     *
     * @return State
     */
    abstract function handler(State $state): State;
}
