<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Sajya\Server\Tests\TestCase;

class ArtisanTest extends TestCase
{
    public function testArtisanMakeProcedure(): void
    {
        $this->artisan('make:procedure', ['name' => 'Test'.time()])
            ->expectsOutput('Procedure created successfully.')
            ->assertExitCode(0);
    }
}
