<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sajya\Server\Commands\ProcedureMakeCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * The available command shortname.
     *
     * @var string[]
     */
    protected array $commands = [
        ProcedureMakeCommand::class,
    ];

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->commands($this->commands);

        Route::macro(
            'rpc',
            fn (string $uri, array $procedures = []) => Route::post($uri, [JsonRpcController::class, '__invoke'])
                ->defaults('procedures', $procedures)
        );
    }
}
