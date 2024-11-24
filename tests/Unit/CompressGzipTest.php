<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Testing\ProceduralRequests;
use Sajya\Server\Tests\TestCase;

class CompressGzipTest extends TestCase
{
    use ProceduralRequests;

    public function test_send_accept_compress_response(): void
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

    public function test_send_without_compress_response(): void
    {
        $this
            ->setRpcRoute('rpc.compress')
            ->callProcedure('fixture@ok')
            ->assertHeaderMissing('content-encoding')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
