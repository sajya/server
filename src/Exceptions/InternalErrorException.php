<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class InternalErrorException extends RpcException
{
    /**
     * InternalErrorException constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Internal JSON-RPC error.
     */
    protected function getDefaultCode(): int
    {
        return -32603;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Internal error';
    }
}
