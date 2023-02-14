<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sajya\Server\Exceptions\InvalidRequestException;
use Sajya\Server\Exceptions\RuntimeRpcException;
use Sajya\Server\Procedure;
use Sajya\Server\Tests\Fixture\RenderResponseException;
use Sajya\Server\Tests\Fixture\ReportException;

class FixtureProcedure extends Procedure
{
    /**
     * The name of the procedure that will be
     * displayed and taken into account in the search.
     */
    public static string $name = 'fixture';

    private Repository $config;

    /**
     * DependencyInjectionProcedure constructor.
     */
    public function __construct(Repository $repository)
    {
        $this->config = $repository;
    }

    public function abort(): void
    {
        abort(404, 'Abort helper');
    }

    public function alwaysResult(): void
    {
    }

    public function dependencyInjection(Request $request)
    {
        return $this->config->get($request->get('0'));
    }

    public function subtract(Request $request): int
    {
        return (int) $request->get('0') - (int) $request->get('1');
    }

    public function division(Request $request): float|int
    {
        return $request->get('a') / $request->get('b');
    }

    public function validationMethod(Request $request): int
    {
        $request->validate([
            'a' => 'integer|required',
            'b' => 'integer|required',
        ]);

        $result = $request->get('a') + $request->get('b');

        Log::info('Result procedure: '.$result);

        return $result;
    }

    public function internalError(): void
    {
        abort(500);
    }

    protected function closeMethod(): string
    {
        return 'Dont Ok';
    }

    public function ok(): string
    {
        return 'Ok';
    }

    public function runtimeError()
    {
        throw new RuntimeRpcException();
    }

    public function invalidRequestException()
    {
        throw new InvalidRequestException([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
    }

    /**
     * @throws \Sajya\Server\Tests\Fixture\ReportException
     */
    public function reportException(): mixed
    {
        throw new ReportException('Report exception');
    }

    /**
     * @throws \Sajya\Server\Tests\Fixture\RenderResponseException
     */
    public function renderException(): mixed
    {
        throw new RenderResponseException();
    }

    /**
     * @return int
     */
    public function payload(Request $request)
    {
        return count($request->all());
    }
}
