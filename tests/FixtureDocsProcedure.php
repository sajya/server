<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Attributes\RpcMethod;
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

    #[
        RpcMethod(
            description: 'Execute the procedure.',
            params: ['key' => 'required', 'array' => 'max:5'],
            result: ['key' => 'string', 'array' => 'max:4'])
    ]
    public function ping(): string
    {
        return 'pong';
    }

    public function empty()
    {
        return 'pong';
    }
}
