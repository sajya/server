<?php

declare(strict_types=1);

namespace Sajya\Server\Exceptions;

use JsonSerializable;
use RuntimeException;

abstract class RpcException extends RuntimeException implements JsonSerializable
{
    /**
     * The value of this member is defined by the Server
     * (e.g. detailed error information, nested errors etc.).
     *
     * @var mixed
     */
    protected $data;

    /**
     * RpcException constructor.
     *
     * @param string|null           $message
     * @param int|null              $code
     * @param RuntimeException|null $previous
     */
    public function __construct(string $message = null, int $code = null, RuntimeException $previous = null)
    {
        parent::__construct(
            $message ?? $this->getDefaultMessage(),
            $code ?? $this->getDefaultCode(),
            $previous
        );
    }

    /**
     * A String providing a short description of the error.
     * The message SHOULD be limited to a concise single sentence.
     *
     * @return string
     */
    abstract protected function getDefaultMessage(): string;

    /**
     * A Number that indicates the error type that occurred.
     * This MUST be an integer.
     *
     * @return int
     */
    abstract protected function getDefaultCode(): int;

    /**
     * A Primitive or Structured value that contains additional information about the error.
     * This may be omitted.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     *
     * @return $this
     */
    public function setData($data = null): RpcException
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array{code:int, message:?string, data:?array}
     */
    public function jsonSerialize(): array
    {
        return [
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'data'    => $this->getData(),
        ];
    }
}
