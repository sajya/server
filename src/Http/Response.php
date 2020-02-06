<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Exception;
use JsonSerializable;

class Response implements JsonSerializable
{
    /**
     * Response error
     *
     * @var null|\Exception
     */
    protected $error;

    /**
     * Request ID
     *
     * @var string|int|null
     */
    protected $id;

    /**
     * Result
     *
     * @var mixed
     */
    protected $result;

    /**
     * JSON-RPC version
     *
     * @var null|string
     */
    protected ?string $version;


    /**
     * @return array
     */
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set request ID
     *
     * @param mixed $name
     *
     * @return self
     */
    public function setId($name)
    {
        $this->id = $name;

        return $this;
    }

    /**
     * Is the response an error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->getError() instanceof Exception;
    }

    /**
     * Get response error
     *
     * @return null|\Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get result.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function setResult($value)
    {
        if ($value instanceof Exception) {
            $this->setError($value);

            return $this;
        }

        $this->result = $value;

        return $this;
    }

    /**
     * Retrieve JSON-RPC version
     *
     * @return null|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set JSON-RPC version.
     *
     * @param string $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $version = (string) $version;
        if ('2.0' === $version) {
            $this->version = '2.0';
            return $this;
        }

        $this->version = null;
        return $this;
    }

    /**
     * Set result error
     *
     * RPC error, if response results in fault.
     *
     * @param Exception|null $error
     *
     * @return Response
     */
    public function setError(Exception $error = null) : Response
    {
        $this->error = $error;

        return $this;
    }

}
