<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Sajya\Server\Facades\RPC;
use Sajya\Server\Tests\FixtureBind;
use Sajya\Server\Tests\TestCase;

class ExpectedTest extends TestCase
{
    public function testAbortWithDebugEnabled(): void
    {
        config()->set('app.debug', true);

        $response = $this->callRPC('testAbort');
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
    }

    public function testAbortWithDebugDisabled(): void
    {
        config()->set('app.debug', false);

        $response = $this->callRPC('testAbort');
        $result = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertFalse(isset(
            $result['file'],
            $result['line'],
            $result['trace']
        ));
    }

    public function testUuidOk(): void
    {
        $this->callRPC('testUuidOk');
    }

    public function testValidationId(): void
    {
        $this->callRPC('testValidationId');
    }

    public function testBatchInvalid(): void
    {
        $this->callRPC('testBatchInvalid');
    }

    public function testBatchNotificationSum(): void
    {
        Log::shouldReceive('info')
            ->twice()
            ->with('Result procedure: 3')
            ->with('Result procedure: 4');

        $this->callRPC('testBatchNotificationSum');
    }

    public function testBatchOneError(): void
    {
        $this->callRPC('testBatchOneError');
    }

    public function testBatchSum(): void
    {
        $this->callRPC('testBatchSum');
    }

    public function testDelimiter(): void
    {
        $this->callRPC('testDelimiter', 'rpc.delimiter');
    }

    public function testDependencyInjection(): void
    {
        $this->callRPC('testDependencyInjection');
    }

    public function testFindMethod(): void
    {
        $this->callRPC('testFindMethod');
    }

    public function testFindProcedure(): void
    {
        $this->callRPC('testFindProcedure');
    }

    public function testInvalidParams(): void
    {
        $this->callRPC('testInvalidParams');
    }

    public function testNotificationSum(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Result procedure: 3');

        $this->callRPC('testNotificationSum');
    }

    public function testNullResult(): void
    {
        $this->callRPC('testNullResult');
    }

    public function testParseError(): void
    {
        $this->callRPC('testParseError');
    }

    public function testSimpleInValidationSum(): void
    {
        $this->callRPC('testSimpleInValidationSum');
    }

    public function testSimpleValidationSum(): void
    {
        $this->callRPC('testSimpleValidationSum');
    }

    public function testWithAnEmptyArray(): void
    {
        $this->callRPC('testWithAnEmptyArray');
    }

    public function testWithAnInvalidBatchButNotEmpty(): void
    {
        $this->callRPC('testWithAnInvalidBatchButNotEmpty');
    }

    public function testWithInvalidBatch(): void
    {
        $this->callRPC('testWithInvalidBatch');
    }

    public function testUnknownVersion(): void
    {
        $this->callRPC('testUnknownVersion');
    }

    public function testInternalError(): void
    {
        $this->callRPC('testInternalError');
    }

    public function testCallCloseMethod(): void
    {
        $this->callRPC('testCallCloseMethod');
    }

    public function testRuntimeError(): void
    {
        $this->callRPC('testRuntimeError');
    }

    public function testInvalidRequestException(): void
    {
        $this->callRPC('testInvalidRequestException');
    }

    public function testCallNoExistMethod(): void
    {
        $this->callRPC('testCallNoExistMethod');
    }

    public function testDivisionException(): void
    {
        $this->callRPC('testDivisionException');
    }

    public function testReportException(): void
    {
        $this->assertNull(config('render-response-exception'));

        $this->callRPC('testReportException');

        $this->assertStringContainsString('Enabled', config('render-response-exception'));
    }

    public function testBindDeepValue(): void
    {
        $this->callRPC('testBindDeepValue');
    }

    public function testBindSubtract(): void
    {
        $this->callRPC('testBindSubtract');
    }

    public function testBindSubtractRewriteBind(): void
    {
        RPC::bind('a', fn () => 100);
        $this->callRPC('testBindSubtractRewriteBind');
    }

    public function testBindModel(): void
    {
        RPC::model('fixtureModel', FixtureBind::class);
        $this->callRPC('testBindModel');
    }

    public function testProxyMethod(): void
    {
        $this->callRPC('testProxyMethod');
    }

    /**
     * Выполняет RPC-запрос.
     *
     * @param string $path
     * @param string $route
     *
     * @return TestResponse
     */
    private function callRPC(string $path, string $route = 'rpc.point'): TestResponse
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
