<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Testing\ProceduralRequests;
use Sajya\Server\Tests\TestCase;

class CompressGzipTest extends TestCase
{
    use ProceduralRequests;

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
            ->assertHeader('Content-Encoding', 'gzip');

        $content = gzdecode($response->getContent());

        $this->assertJson($content);
        $this->assertStringContainsString('"result":"Ok"', $content);
    }

    public function testNoSupportCompress()
    {
        $this
            ->setRpcRoute('rpc.compress')
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
