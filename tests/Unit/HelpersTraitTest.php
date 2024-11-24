<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Testing\ProceduralRequests;
use Sajya\Server\Tests\TestCase;

class HelpersTraitTest extends TestCase
{
    use ProceduralRequests;

    public function test_helper_route_ok(): void
    {
        $this
            ->setRpcRoute('rpc.point')
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }

    public function test_helper_url_ok(): void
    {
        $this
            ->setRpcUrl(route('rpc.point'))
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
