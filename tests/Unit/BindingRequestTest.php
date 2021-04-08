<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Closure;
use Generator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Testing\TestResponse;
use Sajya\Server\Tests\TestCase;

class BindingRequestTest extends TestCase
{
    /**
     * @return Generator
     */
    public function exampleCalls(): Generator
    {
        yield ['testModelBindingSimpleDefaultField', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with(1, null)
                     ->andReturnSelf();
            $userMock->shouldReceive('getAttribute')
                     ->once()
                     ->with('name')
                     ->andReturn('User 1');
            app()->instance(User::class, $userMock);
        }];
        yield ['testModelBindingSimpleCustomField', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with('test@domain.com', 'email')
                     ->andReturnSelf();
            $userMock->shouldReceive('getAttribute')
                     ->once()
                     ->with('name')
                     ->andReturn('User 2');
            app()->instance(User::class, $userMock);
        }];
        yield ['testModelBindingSimpleNestedParameter', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with(3, null)
                     ->andReturnSelf();
            $userMock->shouldReceive('getAttribute')
                     ->once()
                     ->with('name')
                     ->andReturn('User 3');
            app()->instance(User::class, $userMock);
        }];
        yield ['testModelBindingCustomLogic', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with(3)
                     ->andReturnSelf();
            $userMock->shouldReceive('getAttribute')
                     ->once()
                     ->with('name')
                     ->andReturn('User 3');
            app()->instance(User::class, $userMock);
        }];
        yield ['testModelBindingCustomLogicNullable', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with(3)
                     ->andReturnNull();
            $userMock->shouldReceive('get')
                     ->never()
                     ->with('name');
            app()->instance(User::class, $userMock);
        }];
        yield ['testModelBindingCustomLogicNested', function () {
            config()->set('app.debug', true);
            $userMock = \Mockery::mock(User::class);
            $userMock->shouldReceive('resolveRouteBinding')
                     ->once()
                     ->with(3)
                     ->andReturnSelf();
            $userMock->shouldReceive('getAttribute')
                     ->once()
                     ->with('name')
                     ->andReturn('User 3');
            app()->instance(User::class, $userMock);
        }];
    }

    /**
     * @param string       $file
     * @param Closure|null $before
     * @param Closure|null $after
     * @param string       $route
     *
     * @throws \JsonException
     * @dataProvider exampleCalls
     *
     */
    public function testHasCorrectRequestResponse(
        string $file,
        Closure $before = null,
        Closure $after = null,
        string $route = 'rpc.point'
    ): void {
        if ($before !== null) {
            $before();
        }

        $response = $this->callRPC($file, $route);

        if ($after !== null) {
            $after($response);
        }
    }

    /**
     * @param string $path
     * @param string $route
     *
     * @throws \JsonException
     *
     * @return TestResponse
     */
    private function callRPC(string $path, string $route): TestResponse
    {
        $request = file_get_contents("./tests/Expected/Requests/$path.json");
        $response = file_get_contents("./tests/Expected/Responses/$path.json");

        return $this
            ->call('POST', route($route), [], [], [], [], $request)
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson(
                json_decode($response, true, 512, JSON_THROW_ON_ERROR)
            );
    }
    
    /**
     * @testdox We should get an error, if null is returned by {@see BindsParameters::resolveParameter()}
     *          when the related Procedure method does not define the parameter as optional.
     */
    public function testCutomLogicInvalidNull()
    {
        config()->set('app.debug', false);
        $userMock = \Mockery::mock(User::class);
        $userMock->shouldReceive('resolveRouteBinding')
                 ->once()
                 ->with(5)
                 ->andReturn(null);
        app()->instance(User::class, $userMock);
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserNameCustomLogic",
            "params"  => [
                "user" => 5,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            'error' => [
                'code' => -32000,
            ],
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox We should get an error, if the object returned by {@see BindsParameters::resolveParameter()}
     *          does not correspond to the type of object expected by the Procedure method.
     */
    public function testCutomLogicInvalidType()
    {
        config()->set('app.debug', false);
        $userMock = \Mockery::mock(User::class);
        $userMock->shouldReceive('resolveRouteBinding')
                 ->once()
                 ->with(5)
                 ->andReturn(new \stdClass());
        app()->instance(User::class, $userMock);
    
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserNameCustomLogic",
            "params"  => [
                "user" => 5,
            ],
            "jsonrpc" => "2.0",
        ];
    
        $response = [
            'id' => 1,
            'error' => [
                'code' => -32001,
            ],
            "jsonrpc" => "2.0",
        ];
    
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox We should get an error, if the object expected by the Procedure method
     *          does not implement {@see UrlRoutable}, but is expected to be resolved
     *          by the default resolution logic based on {@see BindsParameters::getBindings()}.
     */
    public function testDefaultInvalidType()
    {
        config()->set('app.debug', false);
        $userMock = \Mockery::mock(User::class);
        app()->instance(User::class, $userMock);
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserNameWrong",
            "params"  => [
                "user" => 1,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            'error' => [
                'code' => -32002,
            ],
            "jsonrpc" => "2.0",
        ];
    
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox We should get an error, if the Model instance cannot be resolved
     *          automatically, e.g. due to invalid ID.
     */
    public function testDefaultNotFound()
    {
        config()->set('app.debug', false);
        $userMock = \Mockery::mock(User::class);
        app()->instance(User::class, $userMock);
        $userMock->shouldReceive('resolveRouteBinding')
                 ->once()
                 ->with(1, null)
                 ->andReturnFalse();
    
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserNameDefaultField",
            "params"  => [
                "user" => 1,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            'error' => [
                'code' => -32003,
            ],
            "jsonrpc" => "2.0",
        ];
    
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @param array|string $request
     * @param array        $response
     * @param string       $route
     *
     * @return TestResponse
     * @throws \JsonException
     */
    private function callRpcWith($request, array $response, string $route = 'rpc.point'): TestResponse
    {
        if (!is_string($request)) {
            $request = json_encode($request, JSON_THROW_ON_ERROR);
        }
    
        return $this
            ->call('POST', route($route), [], [], [], [], $request)
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson($response);
    }
}
