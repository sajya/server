<?php

declare(strict_types=1);

namespace Sajya\Server;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use ReflectionClass;
use ReflectionMethod;
use Sajya\Server\Annotations\Param;
use Sajya\Server\Attributes\RpcMethod;

class Docs
{
    /**
     * @var string[]
     */
    protected $procedures;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * Docs constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->procedures = $route->defaults['procedures'];
        $this->delimiter = $route->defaults['delimiter'] ?? '@';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAnnotations(): Collection
    {
        return collect($this->procedures)
            ->map(function (string $class) {
                $reflectionClass = new ReflectionClass($class);
                $name = $reflectionClass->getProperty('name')->getValue();

                return collect($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC))
                    ->map(function (ReflectionMethod $method) use ($name) {

                        $attributes = $this->getMethodAnnotations($method);

                        $request = [
                            'jsonrpc' => '2.0',
                            'id'      => 1,
                            'method'  => $name.$this->delimiter.$method->getName(),
                            'params'  => $attributes?->params,
                        ];

                        $response = [
                            'jsonrpc' => '2.0',
                            'id'      => 1,
                            'result'  => $attributes?->result,
                        ];

                        return [
                            'name'        => $name,
                            'delimiter'   => $this->delimiter,
                            'method'      => $method->getName(),
                            'description' => $attributes?->description,
                            'params'      => $attributes?->params,
                            'result'      => $attributes?->result,
                            'request'     => $this->highlight($request),
                            'response'    => $this->highlight($response),
                        ];
                    });
            })
            ->flatten(1);
    }

    private function getMethodAnnotations(ReflectionMethod $method): ?RpcMethod
    {
        $attributes = $method->getAttributes(RpcMethod::class);

        foreach ($attributes as $attribute) {
            /** @var RpcMethod $instance */
            $instance = $attribute->newInstance();

            return $instance;
        }

        return null;
    }

    /**
     * Highlights a JSON structure using HTML span tags with colors.
     *
     * @param array $value The JSON data to be highlighted.
     *
     * @throws \JsonException If encoding fails.
     *
     * @return \Illuminate\Support\Stringable The highlighted JSON as a string.
     */
    private function highlight(array $value): Stringable
    {
        $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Str::of($json)
            // Highlight keys (both string and numeric)
            ->replaceMatches('/"(\w+)":/i', '"<span style="color:#A0AEC0;">$1</span>":')
            ->replaceMatches('/"(\d+)":/i', '"<span style="color:#A0AEC0;">$1</span>":')

            // Highlight null values
            ->replaceMatches('/":\s*(null)/i', '": <span style="color:#F7768E;">$1</span>')

            // Highlight string values
            ->replaceMatches('/":\s*"([^"]*)"/', '": "<span style="color:#9ECE6A;">$1</span>"')

            // Highlight numeric values
            ->replaceMatches('/":\s*(\d+(\.\d+)?)/', '": <span style="color:#E0AF68;">$1</span>')

            // Highlight boolean values (true/false)
            ->replaceMatches('/":\s*(true|false)/i', '": <span style="color:#7AA2F7;">$1</span>')

            ->wrap('<pre style="color:rgba(212,212,212,0.75);">', '</pre>');
    }

}
