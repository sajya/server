<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     * @param Request $request
     *
     * @return int
     */
    public function handle(Request $request): int
    {
        return (int)$request->get(0) - (int)$request->get(1);
    }
}
