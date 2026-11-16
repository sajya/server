<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Sajya\Server\Attributes\Procedure;
use Sajya\Server\Attributes\RpcMethod;

#[Procedure('docs')]
class FixtureDocsProcedure
{
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
