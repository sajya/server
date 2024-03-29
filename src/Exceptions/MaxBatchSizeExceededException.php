<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

class MaxBatchSizeExceededException extends RpcException
{
    /**
     * MaxBatchSizeExceededException constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();

        $this->setData($data);
    }

    /**
     * Retrieve the error code.
     */
    protected function getDefaultCode(): int
    {
        return -32000;
    }

    /**
     * Retrieve the error message.
     */
    protected function getDefaultMessage(): string
    {
        return 'Maximum batch size exceeded.';
    }
}
