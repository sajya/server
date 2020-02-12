<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class InvalidRequestException extends RpcException
{
    /**
     * InvalidRequestException constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();

        $this->setData($data);
    }

    /**
     * The JSON sent is not a valid Request object.
     */
    protected function getDefaultCode(): int
    {
        return -32600;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Invalid Request';
    }
}
