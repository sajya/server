<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Illuminate\Support\Collection;
use Sajya\Server\Procedure;

class AlwaysResultProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'alwaysResult';

    /**
     * @param Collection $params
     *
     * @return null
     */
    public function handle(Collection $params)
    {
        return null;
    }
}
