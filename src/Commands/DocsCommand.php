<?php

declare(strict_types=1);

namespace Sajya\Server\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Sajya\Server\Docs;

class DocsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sajya:docs {route}
                                       {--name=docs.html : Name of the generated documentation}
                                       {--path=/api/ : Path where included documentation files are located}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation from annotated procedures';

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     *
     * @return int
     */
    public function handle(): int
    {
        $routeName = $this->argument('route');

        $route = Route::getRoutes()->getByName($routeName);

        if ($route === null) {
            $this->warn("Route '$routeName' not found");

            return 1;
        }

        $docs = new Docs($route);

        $html = view('sajya::docs', [
            'title'      => config('app.name'),
            'uri'        => config('app.url') . $route->uri(),
            'procedures' => $docs->getAnnotations(),
        ]);

        Storage::disk()->put($this->option('path') . $this->option('name'), $html->render());
        $this->info('Documentation was generated successfully.');

        return 0;
    }
}
