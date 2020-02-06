<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;

class Request implements JsonSerializable
{
    /**
     * Request ID
     *
     * @var mixed
     */
    protected $id;

    /**
     * Requested method.
     *
     * @var string|null
     */
    protected $method;

    /**
     * Request parameters.
     *
     * @var Collection
     */
    protected $params;

    /**
     * JSON-RPC version of request.
     *
     * @var string
     */
    protected string $version = '2.0';

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->params = collect();
    }

    /**
     * Set request state based on array.
     *
     * @param array $collection
     *
     * @return \Sajya\Server\Http\Request
     */
    public static function loadArray(array $collection): Request
    {
        $request = new static();
        $methods = get_class_methods($request);

        collect($collection)
            ->each(static function ($value, $key) use ($request, $methods) {
                $method = Str::start(ucfirst($key), 'set');

                if (in_array($method, $methods, true)) {
                    $request->$method($value);
                }

                if ($key === 'jsonrpc') {
                    $request->setVersion($value);
                }
            });

        return $request;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $jsonArray['jsonrpc'] = $this->getVersion();
        $jsonArray['method'] = $this->getMethod();

        if ($this->getParams()->isNotEmpty()) {
            $jsonArray['params'] = $this->getParams()->toArray();
        }

        if (null !== ($id = $this->getId())) {
            $jsonArray['id'] = $id;
        }

        return $jsonArray;
    }

    /**
     * Retrieve JSON-RPC version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set JSON-RPC version
     *
     * @param string $version
     *
     * @return Request
     */
    public function setVersion(string $version = '2.0')
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get request method name.
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Set request method.
     *
     * @param string $name
     *
     * @return Request
     */
    public function setMethod(string $name)
    {
        $this->method = $name;

        return $this;
    }

    /**
     * Retrieve parameters.
     *
     * @return Collection
     */
    public function getParams(): Collection
    {
        return $this->params;
    }

    /**
     * Overwrite params.
     *
     * @param array $params
     *
     * @return Request
     */
    public function setParams(array $params)
    {
        $this->params = $this->params->merge($params);

        return $this;
    }

    /**
     * Retrieve request identifier.
     *
     * @return string|null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set request identifier
     *
     * @param mixed $name
     *
     * @return Request
     */
    public function setId($name)
    {
        $this->id = (string) $name;

        return $this;
    }
}
