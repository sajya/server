<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Fixtures;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;

class DependencyInjectionProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search
     *
     * @var string
     */
    public static string $name = 'dependencyInjection';

    /**
     * @var Repository
     */
    private Repository $config;

    /**
     * DependencyInjectionProcedure constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->config = $repository;
    }

    /**
     * @param Request $request
     *
     * @return array|int|string|void
     */
    public function handle(Request $request)
    {
        return $this->config->get($request->get('0'));
    }
}
