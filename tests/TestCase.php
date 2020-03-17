<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Foundation\Application;
use Sajya\Server\Guide;
use Sajya\Server\ServerServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var string[]
     */
    public array $mapProcedures = [
        FixtureProcedure::class,
    ];

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ServerServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['router']->rpc('point', $this->mapProcedures)->name('rpc.point');
    }

    /**
     * @return Guide
     */
    public function getGuide(): Guide
    {
        return new Guide($this->mapProcedures);
    }
}
