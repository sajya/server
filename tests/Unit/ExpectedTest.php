<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Closure;
use Generator;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Sajya\Server\Facades\RPC;
use Sajya\Server\Tests\FixtureBind;
use Sajya\Server\Tests\TestCase;

class ExpectedTest extends TestCase
{
    /**
     * @return Generator
     */
    public function exampleCalls(): Generator
    {
        yield ['testAbort', function () {
            config()->set('app.debug', true);
        }, function (TestResponse $response) {
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

        yield ['testAbort', function () {
            config()->set('app.debug', false);
        }, function (TestResponse $response) {
            $json = $response->getContent();
            $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $this->assertFalse(isset(
                $result['file'],
                $result['line'],
                $result['trace'],
            ));

            config()->set('app.debug', true);
        }];
        yield ['testUuidOk'];
        yield ['testValidationId'];
        yield ['testBatchInvalid'];
        yield ['testBatchNotificationSum', static function () {
            Log::shouldReceive('info')
                ->twice()
                ->with('Result procedure: 3')
                ->with('Result procedure: 4');
        }];
        yield ['testBatchOneError'];
        yield ['testBatchSum'];
        yield ['testDelimiter', null, null, 'rpc.delimiter'];
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
        yield ['testInvalidRequestException'];
        yield ['testCallNoExistMethod'];

        // Exception
        yield ['testDivisionException'];
        yield ['testRenderException'];
        yield ['testReportException', function () {
            $this->assertNull(config('render-response-exception'));
        }, function () {
            $this->assertStringContainsString('Enabled', config('render-response-exception'));
        }];

        // Binding
        yield ['testBindDeepValue',];
        yield ['testBindSubtract',];
        yield ['testBindSubtractRewriteBind', static function () {
            RPC::bind('a', function () {
                return 100;
            });
        }];

        yield ['testBindModel', static function () {
            RPC::model('fixtureModel', FixtureBind::class);
        }];
    }

    /**
     * @param string       $file
     * @param Closure|null $before
     * @param Closure|null $after
     * @param string       $route
     *
     * @throws \JsonException
     * @dataProvider exampleCalls
     *
     */
    public function testHasCorrectRequestResponse(
        string $file,
        Closure $before = null,
        Closure $after = null,
        string $route = 'rpc.point'
    ): void {
        if ($before !== null) {
            $before();
        }

        $response = $this->callRPC($file, $route);

        if ($after !== null) {
            $after($response);
        }
    }

    /**
     * @param string $path
     * @param string $route
     *
     * @throws \JsonException
     *
     * @return TestResponse
     */
    private function callRPC(string $path, string $route): TestResponse
    {
        $request = file_get_contents("./tests/Expected/Requests/$path.json");
        $response = file_get_contents("./tests/Expected/Responses/$path.json");

        return $this
            ->call('POST', route($route), [], [], [], [], $request)
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson(
                json_decode($response, true, 512, JSON_THROW_ON_ERROR)
            );
    }
}
