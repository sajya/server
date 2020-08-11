<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Guide;
use Sajya\Server\Http\Request;
use Sajya\Server\Procedure;
use Sajya\Server\Tests\FixtureProcedure;
use Sajya\Server\Tests\TestCase;

class GuideTest extends TestCase
{
    public function testExtendsProcedure(): void
    {
        $this->expectErrorMessage("Class 'Sajya\Server\Tests\Unit\GuideTest' must extends " . Procedure::class);

        new Guide([
            FixtureProcedure::class,
            GuideTest::class,
        ]);
    }

    public function testFindMethodProcedure(): void
    {
        $request = tap(new Request(), static function (Request $request) {
            $request->setId(1);
            $request->setMethod('fixture@subtract');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertEquals(FixtureProcedure::class.'@subtract', $this->getGuide()->findProcedure($request));
    }

    public function testNotFoundMethodProcedure(): void
    {
        $request = tap(new Request(), static function (Request $request) {
            $request->setId(1);
            $request->setMethod('notFoundMethod');
            $request->setParams([42, 23]);
            $request->setVersion('2.0');
        });

        $this->assertNull($this->getGuide()->findProcedure($request));
    }
}
