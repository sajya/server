<?php

declare(strict_types=1);

namespace Sajya\Server\Testing;

use Illuminate\Testing\TestResponse;

trait ProceduralRequests
{
    /**
     * URL to which procedure requests will be sent
     *
     * @var string|null
     */
    protected ?string $rpcEndpoint;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setRpcRoute(string $name)
    {
        $this->rpcEndpoint = route($name);

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setRpcUrl(string $url)
    {
        $this->rpcEndpoint = $url;

        return $this;
    }

    /**
     * Call the given method procedure and return the Response.
     *
     * @param string          $method
     * @param array           $content
     * @param string|int|null $id
     *
     * @return \Illuminate\Testing\TestResponse
     */
    public function callProcedure(string $method, array $content = [], $id = 1): TestResponse
    {
        return $this
            ->callHttpProcedure($method, $content, $id)
            ->assertOk()
            ->assertHeader('content-type', 'application/json');
    }


    /**
     * @param string          $method
     * @param array           $content
     * @param string|int|null $id
     *
     * @return \Illuminate\Testing\TestResponse
     */
    public function callHttpProcedure(string $method, array $content = [], $id = 1): TestResponse
    {
        return $this->json('POST', $this->rpcEndpoint, [
            'jsonrpc' => '2.0',
            'id'      => $id,
            'method'  => $method,
            'params'  => $content,
        ]);
    }
}
