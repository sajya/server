<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\Fixtures\SubtractProcedure;
use Sajya\Server\Tests\TestCase;
use Sajya\Server\Guide;
use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;

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

        $procedure = new class extends Procedure {

            public static string $name = 'subtract';

            /**
             * @param $a
             * @param $b
             *
             * @return array|int|string|void
             */
            public function handle($a, $b)
            {
                return $a - $b;
            }
        };

        $guide = new Guide([
            $procedure,
        ]);

        $this->assertEquals($procedure, $guide->findProcedure($request));
    }

    public function testNotFoundMethodProcedure(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $guide = new Guide();

        $this->assertNull($guide->findProcedure($request));
    }

    public function testExecuteHandleProcedure(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $guide = new Guide([
            SubtractProcedure::class
        ]);

        $result = $guide->handleProcedure($request);

        $this->assertEquals(19, $result->getResult());
        $this->assertJsonStringEqualsJsonString('{"id":"1","result":19,"jsonrpc":"2.0"}', (string) $result);
    }

    public function testExecuteProcedure(): void
    {
        $guide = new Guide([
            SubtractProcedure::class
        ]);

        $result = $guide->handle('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('"{\"id\":\"1\",\"result\":19,\"jsonrpc\":\"2.0\"}"', $result);
    }
}
