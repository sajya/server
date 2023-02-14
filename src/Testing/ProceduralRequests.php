<?php

declare(strict_types=1);

namespace Sajya\Server\Testing;

use Illuminate\Testing\TestResponse;

trait ProceduralRequests
{
    /**
     * URL to which procedure requests will be sent
     */
    protected ?string $rpcEndpoint;

    /**
     * @return $this
     */
    public function setRpcRoute(string $name)
    {
        $this->rpcEndpoint = route($name);

        return $this;
    }

    /**
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
     * @param string|int|null $id
     */
    public function callProcedure(string $method, array $content = [], $id = 1): TestResponse
    {
        return $this
            ->callHttpProcedure($method, $content, $id)
            ->assertOk()
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * @param string|int|null $id
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
