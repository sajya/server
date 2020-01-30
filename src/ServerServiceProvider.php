<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Commands\MakeProcedureCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * The available command shortname.
     *
     * @var array
     */
    protected $commands = [
        MakeProcedureCommand::class
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

            /* @var Router $this */
            return $this->post($url, fn(Request $request) => $guide($request));
        });
    }
}
