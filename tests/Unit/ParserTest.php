<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Tests\TestCase;

class ParserTest extends TestCase
{
    #[Test]
    public function validJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';

        $content = new Parser($json);

        $this->assertFalse($content->isError());
    }

    #[Test]
    public function invalidJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], id: 1';

        $content = new Parser($json);

        $this->assertTrue($content->isError());
    }

    #[Test]
    public function batchJson(): void
    {
        $json = '[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]';

        $content = new Parser($json);

        $this->assertTrue($content->isBatch());
    }

    #[Test]
    public function notBatchJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';

        $content = new Parser($json);

        $this->assertFalse($content->isBatch());
    }

    #[Test]
    public function parserMakeRequest(): void
    {
        $json = '[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]';

        $content = new Parser($json);
        $requests = $content->makeRequests();

        $this->assertIsArray($requests);
        $request = reset($requests);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals(1, $request->getId());
        $this->assertEquals('subtract', $request->getMethod());
        $this->assertEquals([42, 23], $request->getParams()->toArray());
    }

    #[Test]
    public function notificationJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}';

        $content = new Parser($json);

        $this->assertTrue($content->isNotification());
    }

    #[Test]
    public function bathNotificationJson(): void
    {
        $json = '[
            {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1},
            {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}
        ]';

        $content = new Parser($json);

        $this->assertTrue($content->isNotification());
    }
}
