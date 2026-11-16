<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Sajya\Server\Testing\MakesRpcRequests;
use Sajya\Server\Tests\TestCase;

class MakesRpcRequestsTest extends TestCase
{
    use MakesRpcRequests;

    #[Test]
    public function callsAnRpcMethod(): void
    {
        $this->withRpcEndpoint(route('rpc.point'))
            ->rpc('fixture@ok')
            ->assertOk()
            ->assertJsonPath('result', 'Ok')
            ->assertJsonMissingPath('error');
    }

    #[Test]
    public function passesNamedParameters(): void
    {
        $this->withRpcEndpoint(route('rpc.point'))
            ->rpc('binding@subtract', ['a' => 42, 'b' => 23])
            ->assertOk()
            ->assertJsonPath('result', 19);
    }

    #[Test]
    public function passesPositionalParameters(): void
    {
        $this->withRpcEndpoint(route('rpc.point'))
            ->rpc('fixture@subtract', [42, 23])
            ->assertOk()
            ->assertJsonPath('result', 19);
    }

    #[TestWith([[], 1])]
    #[TestWith([[42, 23], 0])]
    #[TestWith([['a' => 42, 'b' => 23], 'request-1'])]
    #[TestWith([[], '0'])]
    #[TestWith([[], null])]
    #[Test]
    public function preservesTheRequestPayload(array $params, string|int|null $id): void
    {
        Route::post('/rpc/inspect', fn (Request $request) => $request->json()->all());

        $this->withRpcEndpoint('/rpc/inspect')
            ->rpc('math@subtract', $params, $id)
            ->assertExactJson([
                'jsonrpc' => '2.0',
                'method'  => 'math@subtract',
                'params'  => $params,
                'id'      => $id,
            ]);
    }

    #[Test]
    public function returnsHttpErrorsWithoutAssertions(): void
    {
        Route::post('/rpc/forbidden', fn () => response('Forbidden', 403));

        $this->withRpcEndpoint('/rpc/forbidden')
            ->rpc('fixture@ok')
            ->assertForbidden()
            ->assertContent('Forbidden');
    }

    #[Test]
    public function returnsRpcErrors(): void
    {
        $this->withRpcEndpoint(route('rpc.point'))
            ->rpc('missing@method')
            ->assertOk()
            ->assertJsonPath('error.code', -32601)
            ->assertJsonMissingPath('result');
    }

    #[Test]
    public function requiresAnEndpoint(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Set the RPC endpoint using withRpcEndpoint() before making a request.');

        $this->rpc('fixture@ok');
    }
}
