<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Sajya\Server\Testing\ProceduralRequests;
use Sajya\Server\Tests\TestCase;

class HelpersTraitTest extends TestCase
{
    use ProceduralRequests;

    #[Test]
    public function helperRouteOk(): void
    {
        $this
            ->setRpcRoute('rpc.point')
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }

    #[Test]
    public function helperUrlOk(): void
    {
        $this
            ->setRpcUrl(route('rpc.point'))
            ->callProcedure('fixture@ok')
            ->assertJsonFragment([
                'result' => 'Ok',
            ]);
    }
}
