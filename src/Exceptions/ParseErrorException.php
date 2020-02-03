<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class ParseErrorException extends RpcException
{
    /**
     * ParseErrorException constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Invalid JSON was received by the server.
     * An error occurred on the server while parsing the JSON text.
     */
    protected function getDefaultCode(): int
    {
        return -32700;
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     */
    protected function getDefaultMessage(): string
    {
        return 'Parse error';
    }
}
