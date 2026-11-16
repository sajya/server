<?php

declare(strict_types=1);

namespace Sajya\Server\Testing;

use Illuminate\Testing\TestResponse;
use LogicException;

trait MakesRpcRequests
{
    protected ?string $rpcEndpoint = null;

    public function withRpcEndpoint(string $url): static
    {
        $this->rpcEndpoint = $url;

        return $this;
    }

    public function rpc(string $method, array $params = [], string|int|null $id = 1): TestResponse
    {
        if ($this->rpcEndpoint === null) {
            throw new LogicException('Set the RPC endpoint using withRpcEndpoint() before making a request.');
        }

        return $this->postJson($this->rpcEndpoint, [
            'jsonrpc' => '2.0',
            'method'  => $method,
            'params'  => $params,
            'id'      => $id,
        ]);
    }
}
