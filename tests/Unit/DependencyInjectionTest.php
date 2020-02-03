<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class DependencyInjectionTest extends TestCase
{
    public function testExecuteDependencyInjectionProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('{"jsonrpc": "2.0", "method": "dependencyInjection", "params": ["app.name"], "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"result\":\"Laravel\",\"jsonrpc\":\"2.0\"}"', $result);
    }
}
