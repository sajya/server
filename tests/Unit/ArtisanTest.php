<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sajya\Server\Tests\TestCase;

class ArtisanTest extends TestCase
{
    public function testArtisanMakeProcedure(): void
    {
        $this->artisan('make:procedure', ['name' => 'Test' . time()])
            ->expectsOutput('Procedure created successfully.')
            ->assertExitCode(0);
    }

    public function testArtisanMakeDocs(): void
    {
        $this->artisan('sajya:docs', [
            'route' => 'rpc.docs',
        ])
            ->expectsOutput('Documentation was generated successfully.')
            ->assertExitCode(0);
    }

    public function testArtisanMakeDocsForUnknownRoute(): void
    {
        $routeName = Str::random();

        $this->artisan('sajya:docs', [
            'route' => $routeName,
        ])
            ->expectsOutput("Route '$routeName' not found")
            ->assertExitCode(1);
    }

    public function testArtisanMakeDocsForCustomPathName(): void
    {
        $this->artisan('sajya:docs', [
            'route'  => 'rpc.docs',
            '--path' => '/api/2.0/',
            '--name' => 'rpc.html',
        ])
            ->expectsOutput('Documentation was generated successfully.')
            ->assertExitCode(0);

        $this->assertTrue(Storage::disk()->exists('/api/2.0/rpc.html'));
    }
}
