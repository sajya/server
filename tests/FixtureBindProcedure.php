<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Attributes\Procedure;
use Sajya\Server\Http\Request;

#[Procedure('binding')]
class FixtureBindProcedure
{
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
