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
     * @param array $value
     *
     * @throws \JsonException
     *
     * @return \Illuminate\Support\Stringable
     */
    private function highlight(array $value): Stringable
    {
        ini_set('highlight.comment', '#008000');
        ini_set('highlight.default', '#C3E88D');
        ini_set('highlight.html', '#808080');
        ini_set('highlight.keyword', '#998;');
        ini_set('highlight.string', '#d14');

        $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $code = highlight_string('<?php '.$json, true);

        $docs = Str::of($code)
            ->replaceFirst('<span style="color: #C3E88D">&lt;?php </span>', '')
            ->replace('&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;');

        $keys = $this->arrayKeysMulti($value);

        foreach ($keys as $item) {
            $docs = $docs->replace(
                '<span style="color: #d14">"'.$item.'"</span>',
                '<span style="color: #C3E88D">"'.$item.'"</span>'
            );
        }

        foreach ($keys as $item) {
            $docs = $docs->replace(
                '<span style="color: #d14">"'.$item.'"</span>',
                '<span style="color: red">"'.$item.'"</span>'
            );
        }

        return $docs;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function arrayKeysMulti(array $array): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->arrayKeysMulti($value));
            }
        }

        return $keys;
    }
}
