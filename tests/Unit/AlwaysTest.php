<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class AlwaysTest extends TestCase
{
    public function testNullResultProcedure()
    {
        $result = $this->getGuide()
            ->handle('{"jsonrpc": "2.0", "method": "alwaysResult", "params": [], "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"id":"1","result":null,"jsonrpc":"2.0"}', $result);
    }

}
