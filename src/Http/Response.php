<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use JsonSerializable;

class Response implements JsonSerializable
{
    /**
     * Response error.
     *
     * @var null|Exception
     */
    protected $error;

    /**
     * Request ID.
     *
     * @var string|int|null
     */
    protected $id;

    /**
     * Result.
     */
    protected $result;

    /**
     * JSON-RPC version.
     */
    protected ?string $version;

    /**
     * Make Response instance based on result and request.
     */
    public static function makeFromResult($result, Request $request = null): self
    {
        $request ??= new Request();

        return tap(
            new self(),
            fn (Response $response) => $response->setId($request->getId())
                ->setVersion($request->getVersion())
                ->setResult($result)
        );
    }

    public function jsonSerialize(): array
    {
        $response = ['id' => $this->getId()];

        if ($this->isError()) {
            $response['error'] = $this->getError();
        } else {
            $response['result'] = $this->getResult();
        }

        if (null !== ($version = $this->getVersion())) {
            $response['jsonrpc'] = $version;
        }

        return $response;
    }

    /**
     * Get request ID.
     *
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set request ID.
     *
     * @param string|int|null $name
     */
    public function setId($name): self
    {
        $this->id = $name;

        return $this;
    }

    /**
     * Is the response an error?
     */
    public function isError(): bool
    {
        return $this->getError() instanceof Exception;
    }

    /**
     * Get response error.
     */
    public function getError(): ?Exception
    {
        return $this->error;
    }

    /**
     * Get result.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result.
     */
    public function setResult($value): self
    {
        if ($value instanceof Exception) {
            $this->setError($value);

            return $this;
        }

        $this->result = $value;

        return $this;
    }

    /**
     * Retrieve JSON-RPC version.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Set JSON-RPC version.
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set result error.
     *
     * RPC error, if response results in fault.
     */
    public function setError(Exception $error = null): Response
    {
        $this->error = $error;

        return $this;
    }

    public function isNotification(): bool
    {
        return empty($this->getId()) && $this->getError() === null;
    }
}
