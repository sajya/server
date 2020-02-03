<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testAbortProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('{"jsonrpc": "2.0", "method": "abort", "params": ["app.name"], "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"error\":{\"code\":404,\"message\":\"Abort helper\",\"data\":[]},\"jsonrpc\":\"2.0\"}"', $result);

    }
}
