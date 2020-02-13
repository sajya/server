<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Illuminate\Http\Request;
use Sajya\Server\Procedure;

class SumProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'sum';

    /**
     * @param Request $request
     *
     * @return int
     */
    public function handle(Request $request): int
    {
        $request->validate([
            'a' => 'integer|required',
            'b' => 'integer|required',
        ]);


        return $request->get('a') + $request->get('b');
    }
}
