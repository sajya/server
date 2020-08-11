<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sajya\Server\Exceptions\RuntimeRpcException;
use Sajya\Server\Procedure;

class FixtureProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     *
     * @var string
     */
    public static string $name = 'fixture';

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

    public function abort(): void
    {
        abort(404, 'Abort helper');
    }

    /**
     * @return void
     */
    public function alwaysResult(): void
    {
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function dependencyInjection(Request $request)
    {
        return $this->config->get($request->get('0'));
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function subtract(Request $request): int
    {
        return (int)$request->get('0') - (int)$request->get('1');
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function validationMethod(Request $request): int
    {
        $request->validate([
            'a' => 'integer|required',
            'b' => 'integer|required',
        ]);

        $result = $request->get('a') + $request->get('b');

        Log::info('Result procedure: ' . $result);

        return $result;
    }

    public function internalError(): void
    {
        abort(500);
    }

    /**
     * @return string
     */
    protected function closeMethod(): string
    {
        return 'Dont Ok';
    }

    public function runtimeError()
    {
        throw new RuntimeRpcException();
    }
}
