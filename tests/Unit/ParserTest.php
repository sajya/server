<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;
use Sajya\Server\Http\Parser;
use Sajya\Server\Http\Request;

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


    public function testInvalidJsonTwo(): void
    {
        $result = $this->getGuide()->handle('{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}', $result);
    }

   public function testBatchSumProcedure(): void
    {
        $result = $this->getGuide()
            ->handle('[
                {"jsonrpc": "2.0", "method": "sum", "params": {"a": 1, "b": 2}, "id": 1},
                {"jsonrpc": "2.0", "method": "sum", "params": {"a": 3, "b": 4}, "id": 2}
            ]');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('[{"id": 1, "result": 3, "jsonrpc": "2.0"}, {"id": 2, "result": 7, "jsonrpc": "2.0"}]', $result);
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
}
