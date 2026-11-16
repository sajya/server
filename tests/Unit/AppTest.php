<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\App as LaravelApplication;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Sajya\Server\App;
use Sajya\Server\Attributes\Procedure;
use Sajya\Server\HandleProcedure;
use Sajya\Server\Http\Request;
use Sajya\Server\Http\Response;
use Sajya\Server\Tests\FixtureProcedure;
use Sajya\Server\Tests\TestCase;

class AppTest extends TestCase
{
    #[Test]
    public function baseUsage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        /** @var Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
    }

    #[Test]
    public function terminateUsage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        LaravelApplication::partialMock()
            ->shouldReceive('terminate')
            ->once();

        Log::partialMock()
            ->shouldReceive('info')
            ->with('Result procedure: 100800');

        $response = $guide->terminate('{"jsonrpc": "2.0", "method": "fixture@validationMethod", "params": {"a": 100500, "b": 300}}');

        $this->assertNull($response);
    }

    #[Test]
    public function procedureAttributeIsRequired(): void
    {
        $this->expectExceptionMessage("Class 'Sajya\Server\Tests\Unit\AppTest' must have the ".Procedure::class.' attribute');

        new App([
            FixtureProcedure::class,
            AppTest::class,
        ]);
    }

    #[Test]
    public function findMethodProcedure(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('fixture@subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals(FixtureProcedure::class.'@subtract', $this->getGuide()->findProcedure($request));
    }

    #[Test]
    public function notFoundMethodProcedure(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('notFoundMethod');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertNull($this->getGuide()->findProcedure($request));
    }

    #[Test]
    public function notJobDispatched(): void
    {
        Bus::fake();

        $guide = new App([
            FixtureProcedure::class,
        ]);

        /** @var Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
        Bus::assertNothingDispatched();
    }

    #[Test]
    public function notificationRequestJobDispatched(): void
    {
        Bus::fake();

        $guide = new App([
            FixtureProcedure::class,
        ]);

        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok"}');

        $this->assertNull($response);

        Bus::assertDispatchedAfterResponse(HandleProcedure::class);
    }

    #[Test]
    public function ensureBatchSizeWithinLimit(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        $content = collect(range(1, 51))->map(function () {
            return '{"jsonrpc": "2.0", "method": "fixture@ok"}';
        })->implode(',');

        /** @var Response $response */
        $response = $guide->handle('['.$content.']');

        $this->assertEquals('Maximum batch size exceeded.', $response->getError()->getMessage());
    }
}
