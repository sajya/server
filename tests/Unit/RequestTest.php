<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;
use Sajya\Server\Http\Request;

class RequestTest extends TestCase
{
    public function testFillableRequest(): void
    {
        $request = tap(new Request(), function (Request $request) {
            $request->setId(1);
            $request->setMethod('subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });


        $this->assertEquals('2.0', $request->getVersion());
        $this->assertEquals(1, $request->getId());
        $this->assertEquals('subtract', $request->getMethod());
        $this->assertEquals([42, 23], $request->getParams());
    }
}
