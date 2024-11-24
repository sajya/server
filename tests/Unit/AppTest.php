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
    public function test_base_usage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        /** @var \Sajya\Server\Http\Response $response */
        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok", "id": 1}');

        $this->assertEquals('Ok', $response->getResult());
    }

    public function test_terminate_usage(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        $response = $guide->terminate('{"jsonrpc": "2.0", "method": "fixture@validationMethod", "params": {"a": 100500, "b": 300}}');

        Log::shouldReceive('info')->with('Result procedure: 100800');

        $this->assertNull($response);
    }

    public function test_extends_procedure(): void
    {
        $this->expectExceptionMessage("Class 'Sajya\Server\Tests\Unit\AppTest' must extends ".Procedure::class);

        new App([
            FixtureProcedure::class,
            AppTest::class,
        ]);
    }

    public function test_find_method_procedure(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('fixture@subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals(FixtureProcedure::class.'@subtract', $this->getGuide()->findProcedure($request));
    }

    public function test_not_found_method_procedure(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('notFoundMethod');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertNull($this->getGuide()->findProcedure($request));
    }

    public function test_not_job_dispatched(): void
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

    public function test_notification_request_job_dispatched(): void
    {
        Bus::fake();

        $guide = new App([
            FixtureProcedure::class,
        ]);

        $response = $guide->handle('{"jsonrpc": "2.0", "method": "fixture@ok"}');

        $this->assertNull($response);

        Bus::assertDispatchedAfterResponse(HandleProcedure::class);
    }

    public function test_ensure_batch_size_within_limit(): void
    {
        $guide = new App([
            FixtureProcedure::class,
        ]);

        $content = collect(range(1, 51))->map(function () {
            return '{"jsonrpc": "2.0", "method": "fixture@ok"}';
        })->implode(',');

        /** @var \Sajya\Server\Http\Response $response */
        $response = $guide->handle('['.$content.']');

        $this->assertEquals('Maximum batch size exceeded.', $response->getError()->getMessage());
    }
}
