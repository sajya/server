<?php

declare(strict_types=1);

namespace Sajya\Server\Http;

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
     * @var string
     */
    protected $method;

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * JSON-RPC version of request.
     *
     * @var string
     */
    protected $version = '2.0';

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
     * Retrieve param by index or key.
     *
     * @param int|string $index
     *
     * @return mixed|null Null when not found
     */
    public function getParam($index)
    {
        if (!array_key_exists($index, $this->params)) {
            return null;
        }

        return $this->params[$index];
    }

    /**
     * Cast to string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->jsonSerialize();
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string
    {
        $jsonArray['jsonrpc'] = $this->getVersion();
        $jsonArray['method'] = $this->getMethod();

        $params = $this->getParams();

        if (!empty($params)) {
            $jsonArray['params'] = $params;
        }

        if (null !== ($id = $this->getId())) {
            $jsonArray['id'] = $id;
        }

        return json_encode($jsonArray, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * Retrieve JSON-RPC version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set JSON-RPC version
     *
     * @param string $version
     *
     * @return self
     */
    public function setVersion($version = '2.0')
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get request method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set request method.
     *
     * @param string $name
     *
     * @return self
     */
    public function setMethod($name)
    {
        $this->method = $name;

        return $this;
    }

    /**
     * Retrieve parameters.
     *
     * @return array
     */
    public function getParams(): array
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
        $this->params = [];

        return $this->addParams($params);
    }

    /**
     * Add many params.
     *
     * @param array $params
     *
     * @return self
     */
    public function addParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->addParam($value, $key);
        }
        return $this;
    }

    /**
     * Add a parameter to the request.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return self
     */
    public function addParam($value, $key = null)
    {
        if ((null === $key) || !is_string($key)) {
            $index = count($this->params);
            $this->params[$index] = $value;
            return $this;
        }

        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Retrieve request identifier.
     *
     * @return string
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
     * @return self
     */
    public function setId($name)
    {
        $this->id = (string) $name;

        return $this;
    }
}
