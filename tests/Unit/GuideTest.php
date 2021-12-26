<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Sajya\Server\Guide;
use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;
use Sajya\Server\Tests\FixtureProcedure;
use Sajya\Server\Tests\TestCase;

class GuideTest extends TestCase
{
    public function testBaseUsage(): void
    {
        $guide = new Guide([
            FixtureProcedure::class,
        ]);

        /** @var \Sajya\Server\Http\Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
    }

    public function testTerminateUsage(): void
    {
        $guide = new Guide([
            FixtureProcedure::class,
        ]);

        $response = $guide->terminate('{"jsonrpc": "2.0", "method": "fixture@validationMethod", "params": {"a": 100500, "b": 300}}');

        Log::shouldReceive('info')->with('Result procedure: 100800');

        $this->assertNull($response);
    }

    public function testExtendsProcedure(): void
    {
        $this->expectErrorMessage("Class 'Sajya\Server\Tests\Unit\GuideTest' must extends " . Procedure::class);

        new Guide([
            FixtureProcedure::class,
            GuideTest::class,
        ]);
    }

    public function testFindMethodProcedure(): void
    {
        $request = tap(new Request(), static function (Request $request) {
            $request->setId(1);
            $request->setMethod('fixture@subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals(FixtureProcedure::class.'@subtract', $this->getGuide()->findProcedure($request));
    }

    public function testNotFoundMethodProcedure(): void
    {
        $request = tap(new Request(), static function (Request $request) {
            $request->setId(1);
            $request->setMethod('notFoundMethod');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertNull($this->getGuide()->findProcedure($request));
    }
}
