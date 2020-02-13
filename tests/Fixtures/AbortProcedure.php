<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Sajya\Server\Procedure;

class AbortProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'abort';

    /**
     *
     */
    public function handle(): void
    {
        abort(404, 'Abort helper');
    }
}
