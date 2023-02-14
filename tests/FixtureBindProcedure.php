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
     */
    public static string $name = 'binding';

    public function deepValue(Request $request, string $nameDeepValue): string
    {
        return $request->getMethod().' '.$nameDeepValue;
    }

    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    public function getModel(FixtureBind $fixtureModel): string
    {
        return (string) $fixtureModel;
    }
}
