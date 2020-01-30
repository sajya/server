<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Foundation\Application;
use Sajya\Server\ServerServiceProvider;
use Sajya\Server\Tests\Fixtures\SubtractProcedure;

class TestCase extends \Orchestra\Testbench\TestCase
{

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

        $app['router']->rpc('point', [
            SubtractProcedure::class,
        ])->name('rpc.point');


    }

}
