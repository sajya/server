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
        $name = 'Test'.time();

        $this->artisan('make:procedure', ['name' => $name])
            ->expectsOutputToContain($name)
            ->assertOk();

        $this->assertFileExists(app_path("Http/Procedures/$name.php"));
    }

    public function testArtisanMakeDocs(): void
    {
        $this->artisan('sajya:docs', [
            'route' => 'rpc.docs',
        ])
            ->expectsOutputToContain('Documentation was generated successfully.')
            ->assertExitCode(0);
    }

    public function testArtisanMakeDocsForUnknownRoute(): void
    {
        $routeName = Str::random();

        $this->artisan('sajya:docs', [
            'route' => $routeName,
        ])
            ->expectsOutputToContain("Route '$routeName' not found")
            ->assertExitCode(1);
    }

    public function testArtisanMakeDocsForCustomPathName(): void
    {
        $this->artisan('sajya:docs', [
            'route'  => 'rpc.docs',
            '--path' => '/api/2.0/',
            '--name' => 'rpc.html',
        ])
            ->expectsOutputToContain('Documentation was generated successfully.')
            ->assertExitCode(0);

        $this->assertTrue(Storage::disk()->exists('/api/2.0/rpc.html'));
    }
}
