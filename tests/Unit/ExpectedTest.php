<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Sajya\Server\Facades\RPC;
use Sajya\Server\Tests\FixtureBind;
use Sajya\Server\Tests\TestCase;

class ExpectedTest extends TestCase
{
    private const REQUESTS_PATH = './tests/Expected/Requests/';
    private const RESPONSES_PATH = './tests/Expected/Responses/';

    /**
     * Test with debug true and response structure validation
     */
    #[Test]
    public function abortWithDebugTrue(): void
    {
        config()->set('app.debug', true);

        $response = $this->hasCorrectRequestResponse('testAbort');

        $response->assertJsonStructure([
            'id',
            'error' => ['code', 'message', 'data', 'file', 'line', 'trace'],
            'jsonrpc',
        ]);
    }

    /**
     * Test with debug false and absence of error details
     */
    #[Test]
    public function abortWithDebugFalse(): void
    {
        config()->set('app.debug', false);

        $response = $this->hasCorrectRequestResponse('testAbort');
        $result = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayNotHasKey('file', $result);
        $this->assertArrayNotHasKey('line', $result);
        $this->assertArrayNotHasKey('trace', $result);

        config()->set('app.debug', true);
    }

    /**
     * Test logging for batch notification sum
     */
    #[Test]
    public function batchNotificationSum(): void
    {
        Log::shouldReceive('info')
            ->twice()
            ->with('Result procedure: 3')
            ->with('Result procedure: 4');

        Log::shouldReceive('error')
            ->never();

        $this->hasCorrectRequestResponse('testBatchNotificationSum');
    }

    /**
     * Test logging for single notification sum
     */
    #[Test]
    public function notificationSum(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Result procedure: 3');

        Log::shouldReceive('error')
            ->never();

        $this->hasCorrectRequestResponse('testNotificationSum');
    }

    /**
     * Test provider with exception response validation
     */
    #[Test]
    public function reportException(): void
    {
        $this->assertNull(config('render-response-exception'));

        $this->hasCorrectRequestResponse('testReportException');

        $this->assertStringContainsString('Enabled', config('render-response-exception'));
    }

    /**
     * Test with rewritten binding for subtraction
     */
    #[Test]
    public function bindSubtractRewriteBind(): void
    {
        RPC::bind('a', fn () => 100);

        $this->hasCorrectRequestResponse('testBindSubtractRewriteBind');
    }

    /**
     * Test model binding for fixture
     */
    #[Test]
    public function bindModel(): void
    {
        RPC::model('fixtureModel', FixtureBind::class);

        $this->hasCorrectRequestResponse('testBindModel');
    }

    /**
     * Helper for making requests and validating responses
     *
     * @param string $path
     * @param string $route
     *
     * @return TestResponse
     */
    #[TestWith(['testUuidOk'])]
    #[TestWith(['testValidationId'])]
    #[TestWith(['testBatchInvalid'])]
    #[TestWith(['testBatchOneError'])]
    #[TestWith(['testBatchSum'])]
    #[TestWith(['testDelimiter', 'rpc.delimiter'])]
    #[TestWith(['testDependencyInjection'])]
    #[TestWith(['testFindMethod'])]
    #[TestWith(['testFindProcedure'])]
    #[TestWith(['testInvalidParams'])]
    #[TestWith(['testNullResult'])]
    #[TestWith(['testParseError'])]
    #[TestWith(['testSimpleInValidationSum'])]
    #[TestWith(['testSimpleValidationSum'])]
    #[TestWith(['testWithAnEmptyArray'])]
    #[TestWith(['testWithAnInvalidBatchButNotEmpty'])]
    #[TestWith(['testWithInvalidBatch'])]
    #[TestWith(['testUnknownVersion'])]
    #[TestWith(['testInternalError'])]
    #[TestWith(['testCallCloseMethod'])]
    #[TestWith(['testRuntimeError'])]
    #[TestWith(['testInvalidRequestException'])]
    #[TestWith(['testCallNoExistMethod'])]
    #[TestWith(['testDivisionException'])]
    #[TestWith(['testBindDeepValue'])]
    #[TestWith(['testBindSubtract'])]
    #[TestWith(['testProxyMethod'])]
    #[Test]
    public function hasCorrectRequestResponse(string $path, string $route = 'rpc.point'): TestResponse
    {
        $request = file_get_contents(self::REQUESTS_PATH."$path.json");
        $response = file_get_contents(self::RESPONSES_PATH."$path.json");

        return $this
            ->call('POST', route($route), [], [], [], [], $request)
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson(json_decode($response, true, 512, JSON_THROW_ON_ERROR));
    }
}
