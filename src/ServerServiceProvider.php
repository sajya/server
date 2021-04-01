<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sajya\Server\Commands\DocsCommand;
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
        DocsCommand::class,
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerViews();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->commands($this->commands);
        $this->registerViews();

        Route::macro('rpc', fn(string $uri, array $procedures = [], string $delimiter = null) => Route::post($uri, [JsonRpcController::class, '__invoke'])
            ->setDefaults([
                'procedures' => $procedures,
                'delimiter'  => $delimiter,
            ]));
    }

    /**
     * Register views & Publish views.
     *
     * @return $this
     */
    public function registerViews(): self
    {
        $path = __DIR__ . '/../views';

        $this->loadViewsFrom($path, 'sajya');

        $this->publishes([
            $path => resource_path('views/vendor/sajya'),
        ], 'views');

        return $this;
    }
}
