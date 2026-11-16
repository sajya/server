<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Testing\MakesRpcRequests;
use Sajya\Server\Tests\TestCase;

class CompressGzipTest extends TestCase
{
    use MakesRpcRequests;

    public function testSendAcceptCompressResponse(): void
    {
        $response = $this->json('POST', route('rpc.compress'), [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'fixture@ok',
        ], [
            'Accept-Encoding' => 'gzip',
        ])
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('content-encoding', 'gzip');

        $content = gzdecode($response->getContent());

        $this->assertJson($content);
        $this->assertStringContainsString('"result":"Ok"', $content);
    }

    public function testSendWithoutCompressResponse(): void
    {
        $this
            ->withRpcEndpoint(route('rpc.compress'))
            ->rpc('fixture@ok')
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertHeaderMissing('content-encoding')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
