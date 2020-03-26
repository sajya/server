<?php

declare(strict_types=1);

namespace Sajya\Server\Commands;

use Illuminate\Console\GeneratorCommand;

class ProcedureMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:procedure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new procedure class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Procedure';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/procedure.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Procedures';
    }
}
