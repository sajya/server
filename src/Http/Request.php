<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;

class Request implements JsonSerializable
{
    /**
     * Request ID.
     */
    protected $id;

    /**
     * Requested method.
     */
    protected ?string $method;

    /**
     * Request parameters.
     */
    protected Collection $params;

    /**
     * JSON-RPC version of request.
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
     */
    public static function loadArray(array $collection): Request
    {
        $request = new static();
        $methods = get_class_methods($request);

        collect($collection)
            ->each(static function ($value, string $key) use ($request, $methods) {
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

    public function jsonSerialize(): array
    {
        $jsonArray = [
            'jsonrpc' => $this->getVersion(),
            'method'  => $this->getMethod(),
        ];

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
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set JSON-RPC version.
     */
    public function setVersion(string $version = '2.0'): Request
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get request method name.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Set request method.
     */
    public function setMethod(string $name): Request
    {
        $this->method = $name;

        return $this;
    }

    /**
     * Retrieve parameters.
     */
    public function getParams(): Collection
    {
        return $this->params;
    }

    /**
     * Overwrite params.
     */
    public function setParams(array $params): self
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
     * Set request identifier.
     *
     * @param int|string $name
     */
    public function setId($name): Request
    {
        $this->id = (string) $name;

        return $this;
    }

    public function isNotification(): bool
    {
        return empty($this->getId());
    }
}
