<?php

declare(strict_types=1);

namespace Sajya\Server;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sajya\Server\Commands\ProcedureMakeCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * The available command shortname.
     *
     * @var array
     */
    protected array $commands = [
        ProcedureMakeCommand::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {

    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->commands($this->commands);

        AnnotationRegistry::registerLoader('class_exists');

        Route::macro('rpc',
            fn(string $uri, array $procedures = []) => Route::match(['POST'], $uri, JsonRpcController::class)
                ->defaults('procedures', $procedures)
        );
    }
}
