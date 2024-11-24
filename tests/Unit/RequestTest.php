<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Http\Request;
use Sajya\Server\Tests\TestCase;

class RequestTest extends TestCase
{
    public function test_fillable_request(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals(1, $request->getId());
        $this->assertEquals('subtract', $request->getMethod());
        $this->assertEquals([42, 23], $request->getParams()->toArray());
    }

    public function test_revert_request(): void
    {
        $request = tap(new Request, static function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $json = json_encode($request, JSON_THROW_ON_ERROR, 512);

        $this->assertJson($json);
        $this->assertEquals('{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"1"}', $json);
    }
}
