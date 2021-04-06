<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Foundation\Auth\User;
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
        return (int) $request->get('0') - (int) $request->get('1');
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

        Log::info('Result procedure: '.$result);

        return $result;
    }
    
    /**
     * @param FixtureRequest $request
     * @param User           $userById User resolved by the default resolution logic using ID as key.
     *
     * @return string
     */
    public function getUserNameDefaultKey(FixtureRequest $request, User $userById): string
    {
        return $userById->getAttribute('name');
    }
    
    /**
     * @param FixtureRequest $request
     * @param User           $userByEmail User resolved by the default resolution logic using Email as key.
     *
     * @return string
     */
    public function getUserNameCustomKey(FixtureRequest $request, User $userByEmail): string
    {
        return $userByEmail->getAttribute('name');
    }
    
    /**
     * @param FixtureRequest $request
     * @param User           $userCustom User resolved by the custom resolution logic.
     *
     * @return string
     */
    public function getUserNameCustomLogic(FixtureRequest $request, User $userCustom): string
    {
        return $userCustom->getAttribute('name');
    }
    
    /**
     * @param FixtureRequest $request
     * @param null|User      $userCustom User resolved by the custom resolution logic.
     *
     * @return string
     */
    public function getUserNameCustomLogicNullable(FixtureRequest $request, ?User $userCustom = null): string
    {
        return is_null($userCustom) ? 'No user' : $userCustom->getAttribute('name');
    }
    
    /**
     * @param FixtureRequest $request
     * @param Filesystem $wrongTypeVar Should trigger an exception, because
     *                                 it does not implement {@see UrlRoutable}.
     *
     * @return string
     */
    public function getUserNameWrong(FixtureRequest $request, Filesystem $wrongTypeVar): string
    {
        return gettype($wrongTypeVar);
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

    /**
     * @return string
     */
    public function ok(): string
    {
        return 'Ok';
    }

    public function runtimeError()
    {
        throw new RuntimeRpcException();
    }
}
