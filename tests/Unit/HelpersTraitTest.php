<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Testing\ProceduralRequests;
use Sajya\Server\Tests\TestCase;

class HelpersTraitTest extends TestCase
{
    use ProceduralRequests;

    public function setUp(): void
    {
        parent::setUp();

        $this->setRpcRoute('rpc.point');
    }

    public function testHelperOk(): void
    {
        $this
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
