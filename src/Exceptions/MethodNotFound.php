<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class MethodNotFound extends RpcException
{
    /**
     * MethodNotFound constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * The method does not exist / is not available.
     */
    protected function getDefaultCode(): int
    {
        return -32601;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Method not found';
    }
}
