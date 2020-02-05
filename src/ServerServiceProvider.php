<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
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
        ProcedureMakeCommand::class
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

        Route::macro('rpc', function ($url, array $map = []) {
            $guide = app()->make(Guide::class, $map);

            return Route::post($url, fn(Request $request) => $guide($request));
        });
    }
}
