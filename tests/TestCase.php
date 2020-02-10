<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Foundation\Application;
use Sajya\Server\Guide;
use Sajya\Server\ServerServiceProvider;
use Sajya\Server\Tests\Fixtures\AbortProcedure;
use Sajya\Server\Tests\Fixtures\AlwaysResultProcedure;
use Sajya\Server\Tests\Fixtures\DependencyInjectionProcedure;
use Sajya\Server\Tests\Fixtures\SubtractProcedure;
use Sajya\Server\Tests\Fixtures\SumProcedure;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var string[]
     */
    public array $mapProcedures = [
        SubtractProcedure::class,
        DependencyInjectionProcedure::class,
        SumProcedure::class,
        AbortProcedure::class,
        AlwaysResultProcedure::class,
    ];


    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
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
    protected function getEnvironmentSetUp($app)
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
