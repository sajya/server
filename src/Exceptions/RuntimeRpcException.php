<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

namespace Sajya\Server\Exceptions;

class RuntimeRpcException extends RpcException
{
    protected function getDefaultMessage(): string
    {
        return 'Unknown';
    }

    protected function getDefaultCode(): int
    {
        return -1;
    }

    /**
     * Report the exception.
     */
    public function report(): ?bool
    {
        return true;
    }
}
