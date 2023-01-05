<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Sajya\Server\App;
use Sajya\Server\HandleProcedure;
use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;
use Sajya\Server\Tests\FixtureProcedure;
use Sajya\Server\Tests\TestCase;

class AppTest extends TestCase
{
    public function testBaseUsage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        /** @var \Sajya\Server\Http\Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
    }

    public function testTerminateUsage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        $response = $guide->terminate('{"jsonrpc": "2.0", "method": "fixture@validationMethod", "params": {"a": 100500, "b": 300}}');

        Log::shouldReceive('info')->with('Result procedure: 100800');

        $this->assertNull($response);
    }

    public function testExtendsProcedure(): void
    {
        $this->expectErrorMessage("Class 'Sajya\Server\Tests\Unit\AppTest' must extends " . Procedure::class);

        new App([
            FixtureProcedure::class,
            AppTest::class,
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

    public function testNotJobDispatched(): void
    {
        Bus::fake();

        $guide = new App([
            FixtureProcedure::class,
        ]);

        /** @var \Sajya\Server\Http\Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
        Bus::assertNothingDispatched();
    }

    public function testNotificationRequestJobDispatched(): void
    {
        Bus::fake();

        $guide = new App([
            FixtureProcedure::class,
        ]);

        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok"}');

        $this->assertNull($response);

        Bus::assertDispatchedAfterResponse(HandleProcedure::class);
    }
}
