<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Http\Request;
use Sajya\Server\Tests\Fixtures\SubtractProcedure;
use Sajya\Server\Tests\TestCase;


class GuideTest extends TestCase
{
    public function testFindMethodProcedure(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals(new SubtractProcedure(), $this->getGuide()->findProcedure($request));
    }

    public function testNotFoundMethodProcedure(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('notFoundMethod');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertNull($this->getGuide()->findProcedure($request));
    }

    public function testExecuteHandleProcedure(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $result = $this->getGuide()->handleProcedure($request);

        $this->assertEquals(19, $result->getResult());
        $this->assertJsonStringEqualsJsonString('{"id":"1","result":19,"jsonrpc":"2.0"}', (string)$result);
    }

    public function testExecuteSubtractProcedure(): void
    {
        $result = $this->getGuide()->handle('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"result\":19,\"jsonrpc\":\"2.0\"}"', $result);
    }
}
