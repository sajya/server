<?php

declare(strict_types=1);

namespace Sajya\Server\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Testing\TestResponse;
use Sajya\Server\Facades\RPC;
use Sajya\Server\Tests\FixtureProcedure;
use Sajya\Server\Tests\TestCase;

/**
 * Tests the {@see RPC::bind()} and {@see RPC::model()} global binding and Procedure method parameter injection.
 */
class BindingGlobalTest extends TestCase
{
    /**
     * @testdox Simple case of bind.
     */
    public function testFacadeBind()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(5, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            }
        );
    
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 5,
            ],
            "jsonrpc" => "2.0",
        ];
    
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
    
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using nested parameter.
     */
    public function testFacadeBindNestedParameter()
    {
        RPC::bind(
            ['customer','user'],
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(6, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            }
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "customer" => [
                    'title' => 'Dr.',
                    'user' => 6
                ],
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using nested parameter with a name other than the Procedure method parameter's name..
     */
    public function testFacadeBindNestedParameter2()
    {
        RPC::bind(
            ['user','id'],
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(6, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            '',
            'user' // Since now we bind the 'id', but the method parameter is called '$user'
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => [
                    'title' => 'Dr.',
                    'id' => 6
                ],
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using a Procedure::method scope declared as string.
     */
    public function testFacadeBindTargetStringClassMethod()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            'Sajya\Server\Tests\FixtureProcedure@getUserName'
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using a Procedure scope declared as string.
     */
    public function testFacadeBindTargetStringClass()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            'Sajya\Server\Tests\FixtureProcedure'
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using a Procedure scope declared as string.
     */
    public function testFacadeBindTargetNamespace()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            'Sajya\Server\Tests'
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using a Procedure::method scope declared as PHP callable.
     */
    public function testFacadeBindTargetCallable()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            [FixtureProcedure::class,'getUserName']
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Bind using multiple target scopes.
     */
    public function testFacadeBindMultipleTarget()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            [
                'Sajya\Server\Tests\FixtureProcedure@subtract',
                [FixtureProcedure::class,'getUserName'],
                'Sajya\Server\Tests\FixtureProcedure@getUserNameDefaultKey'
            ]
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Multiple scoped bindings.
     */
    public function testFacadeBindMultipleBinds()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldNotReceive('getAttribute');
                return $userMock;
            },
            'Sajya\Server\Tests\FixtureProcedure@subtract'
        );
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            [FixtureProcedure::class,'getUserName']
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Multiple scoped bindings should be applied in order they are defined.
     */
    public function testFacadeBindMultipleBindsPriority()
    {
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                self::assertEquals(7, $parameter);
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Custom User');
                return $userMock;
            },
            ''
        );
        RPC::bind(
            'user',
            /**
             * @param  string  $parameter
             * @return User|Authenticatable
             */
            static function (string $parameter) {
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldNotReceive('getAttribute');
                return $userMock;
            },
            [FixtureProcedure::class,'getUserName']
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 7,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Custom User",
            "jsonrpc" => "2.0",
        ];
        
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Simple case of model.
     */
    public function testFacadeModel()
    {
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
        
        RPC::model('user', User::class);
    
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 3,
            ],
            "jsonrpc" => "2.0",
        ];
    
        $response = [
            'id' => 1,
            "result" => "User 3",
            "jsonrpc" => "2.0",
        ];
    
        return $this->callRpcWith($request, $response);
    }
    
    /**
     * @testdox Failure callback should be called, if automatic model binding fails.
     */
    public function testFacadeModelFailureCallback()
    {
        $userMock = \Mockery::mock(User::class);
        $userMock->shouldReceive('resolveRouteBinding')
                 ->once()
                 ->with(3)
                 ->andReturnFalse();
        $userMock->shouldNotReceive('getAttribute');
        app()->instance(User::class, $userMock);
        
        RPC::model(
            'user',
            User::class,
            '',
            'user',
            /**
             * @return User|Authenticatable
             */
            static function () {
                $userMock = \Mockery::mock(User::class);
                $userMock->shouldReceive('getAttribute')
                         ->once()
                         ->with('name')
                         ->andReturn('Fallback User');
                return $userMock;
            }
        );
        
        $request = [
            "id"      => 1,
            "method"  => "fixture@getUserName",
            "params"  => [
                "user" => 3,
            ],
            "jsonrpc" => "2.0",
        ];
        
        $response = [
            'id' => 1,
            "result" => "Fallback User",
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
