<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class RulesValidationTest extends TestCase
{
    public function testSimpleValidationSumProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('{"jsonrpc": "2.0", "method": "sum", "params": {"a": 1, "b": 2}, "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"result\":3,\"jsonrpc\":\"2.0\"}"', $result);
    }

    public function testSimpleInValidationSumProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('{"jsonrpc": "2.0", "method": "sum", "params": {"a": "foo", "b": "bar"}, "id": 1}');


        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"error\":{\"code\":-32602,\"message\":\"Invalid params\",\"data\":{\"a\":[\"The a must be an integer.\"],\"b\":[\"The b must be an integer.\"]}},\"jsonrpc\":\"2.0\"}"', $result);
    }
}
