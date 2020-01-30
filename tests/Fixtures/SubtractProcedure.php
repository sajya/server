<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Sajya\Server\Procedure;

class SubtractProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'subtract';

    /**
     * @param $a
     * @param $b
     *
     * @return array|int|string|void
     */
    public function handle($a, $b)
    {
        return $a - $b;
    }
}
