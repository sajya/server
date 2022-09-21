<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Annotations\Param;
use Sajya\Server\Annotations\Result;
use Sajya\Server\Procedure;

class FixtureDocsProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     *
     * @var string
     */
    public static string $name = 'docs';

    /**
     * Execute the procedure.
     *
     * @Param(name="key", value="required")
     * @Param(name="array.", value="max:5")
     * @Result(name="key", value="string")
     * @Result(name="array.", value="max:4")
     *
     * @return string
     */
    public function ping(): string
    {
        return 'pong';
    }

    public function empty()
    {
        return 'pong';
    }
}
