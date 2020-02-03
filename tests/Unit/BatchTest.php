<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class BatchTest extends TestCase
{
    public function testBatchSumProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('[
                {"jsonrpc": "2.0", "method": "sum", "params": {"a": 1, "b": 2}, "id": 1},
                {"jsonrpc": "2.0", "method": "sum", "params": {"a": 3, "b": 4}, "id": 2}
            ]');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('["{\"id\":\"1\",\"result\":3,\"jsonrpc\":\"2.0\"}","{\"id\":\"2\",\"result\":7,\"jsonrpc\":\"2.0\"}"]', $result);
    }
}
