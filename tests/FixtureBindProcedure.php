<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;

class FixtureBindProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     *
     * @var string
     */
    public static string $name = 'binding';

    /**
     * @param Request $request
     * @param string  $nameForBindValue
     *
     * @return string
     */
    public function deepValue(Request $request, string $nameDeepValue): string
    {
        return $nameDeepValue;
    }

    /**
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    /**
     * @param string $bind
     *
     * @return string
     */
    public function getModel(FixtureBind $fixtureModel): string
    {
        return (string)$fixtureModel;
    }
}
