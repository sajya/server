<?php

declare(strict_types=1);

namespace Sajya\Server\Lines;

use Sajya\Server\Line;
use Sajya\Server\State;

class ValidationRequestFormat extends Line
{
    /**
     * @param State $state
     *
     * @return State
     */
    public function handler(State $state): State
    {
        return $state;
    }
}
