<?php

declare(strict_types=1);

namespace Sajya\Server;

abstract class Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     *
     * Must be unique.
     *
     * @var string
     */
    public static string $name;
}
