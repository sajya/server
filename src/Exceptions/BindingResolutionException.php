<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class BindingResolutionException extends RpcException
{
    /**
     * InternalErrorException constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();

        $this->setData($data);
    }

    /**
     * Internal JSON-RPC error.
     */
    protected function getDefaultCode(): int
    {
        return -32000;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Custom resolution logic returned `null`, but parameter is not optional.';
    }
}
