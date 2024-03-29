<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class InvalidParams extends RpcException
{
    /**
     * InvalidParams constructor.
     *
     * @param mixed|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();

        $this->setData($data);
    }

    /**
     * Invalid method parameter(s).
     */
    protected function getDefaultCode(): int
    {
        return -32602;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Invalid params';
    }
}
