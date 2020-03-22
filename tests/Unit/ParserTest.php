<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;
use Sajya\Server\Tests\TestCase;

class ParserTest extends TestCase
{
    public function testValidJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';

        $content = new Parser($json);

        $this->assertFalse($content->isError());
    }

    public function testInvalidJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], id: 1';

        $content = new Parser($json);

        $this->assertTrue($content->isError());
    }

    public function testBatchJson(): void
    {
        $json = '[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]';

        $content = new Parser($json);

        $this->assertTrue($content->isBatch());
    }

    public function testNotBatchJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';

        $content = new Parser($json);

        $this->assertFalse($content->isBatch());
    }

    public function testParserMakeRequest(): void
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

    public function testNotificationJson(): void
    {
        $json = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}';

        $content = new Parser($json);

        $this->assertTrue($content->isNotification());
    }

    public function testBathNotificationJson(): void
    {
        $json = '[
            {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1},
            {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23]}
        ]';

        $content = new Parser($json);

        $this->assertTrue($content->isNotification());
    }
}
