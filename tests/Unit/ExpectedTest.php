<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Closure;
use Generator;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Sajya\Server\Tests\TestCase;
use Throwable;

class ExpectedTest extends TestCase
{
    /**
     * @return Generator
     */
    public function exampleCalls(): Generator
    {
        yield ['testAbort', null, function ($response) {
            $response->assertJsonStructure([
                'id',
                'error' => [
                    'code',
                    'message',
                    'data',
                    'file',
                    'line',
                    'trace',
                ],
                'jsonrpc',
            ]);
        }];

        yield ['testBatchInvalid'];
        yield ['testBatchNotificationSum', static function () {
            Log::shouldReceive('info')
                ->twice()
                ->with('Result procedure: 3')
                ->with('Result procedure: 4');
        }];
        yield ['testBatchOneError'];
        yield ['testBatchSum'];
        yield ['testDependencyInjection'];
        yield ['testFindProcedure'];
        yield ['testInvalidParams'];
        yield ['testNotificationSum', static function () {
            Log::shouldReceive('info')
                ->once()
                ->with('Result procedure: 3');
        }];
        yield ['testNullResult'];
        yield ['testParseError'];
        yield ['testSimpleInValidationSum'];
        yield ['testSimpleValidationSum'];
        yield ['testWithAnEmptyArray'];
        yield ['testWithAnInvalidBatchButNotEmpty'];
        yield ['testWithInvalidBatch'];
        yield ['testUnknownVersion'];
        yield ['testInternalError'];
        yield ['testCallCloseMethod'];
        yield ['testRuntimeError'];
    }

    /**
     * @param string  $file
     * @param Closure $before
     * @param Closure $after
     *
     * @dataProvider exampleCalls
     *
     * @throws Throwable
     */
    public function testHasCorrectRequestResponse(string $file, Closure $before = null, Closure $after = null): void
    {
        if ($before !== null) {
            $before();
        }

        $response = $this->callRPC($file);

        if ($after !== null) {
            $after($response);
        }
    }

    /**
     * @param string $path
     *
     * @throws \JsonException
     *
     * @return TestResponse
     */
    private function callRPC(string $path): TestResponse
    {
        $request = file_get_contents("./tests/Expected/Requests/$path.json");
        $response = file_get_contents("./tests/Expected/Responses/$path.json");

        return $this
            ->call('POST', route('rpc.point'), [], [], [], [], $request)
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson(
                json_decode($response, true, 512, JSON_THROW_ON_ERROR)
            );
    }
}
