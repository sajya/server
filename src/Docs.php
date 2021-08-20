<?php

declare(strict_types=1);

namespace Sajya\Server;

use Doctrine\Common\Annotations\AnnotationReader;
use Illuminate\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use Sajya\Server\Annotations\Param;
use Sajya\Server\Annotations\Result;

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
                        $request = [
                            "jsonrpc" => "2.0",
                            "id"      => 1,
                            "method"  => $name . $this->delimiter . $method->getName(),
                            'params'  => $this->getMethodAnnotations($method, Param::class),
                        ];

                        $response = [
                            "jsonrpc" => "2.0",
                            "id"      => 1,
                            'result'  => $this->getMethodAnnotations($method, Result::class),
                        ];


                        $factory = DocBlockFactory::createInstance();
                        $comment = $method->getDocComment();
                        $docblock = $factory->create($comment === false ? ' ' : $comment);
                        $description = $docblock->getSummary();

                        return [
                            'name'        => $name,
                            'description' => $description,
                            'delimiter'   => $this->delimiter,
                            'method'      => $method->getName(),
                            'request'     => $this->highlight($request),
                            'response'    => $this->highlight($response),
                        ];
                    });
            })
            ->flatten(1);
    }

    /**
     * @param ReflectionMethod $method
     * @param string           $class
     *
     * @return array
     */
    private function getMethodAnnotations(ReflectionMethod $method, string $class): array
    {
        $repository = new Repository();

        $values = $this
            ->getAnnotationsFrom($method, $class)
            ->mapWithKeys(fn (object $param) => [$param->name => $param->value]);

        foreach ($values as $key => $param) {
            $key = Str::of($key);

            if ($key->endsWith('.')) {
                $repository->push((string)$key->replaceLast('.', ''), $param);
            } else {
                $repository->set((string)$key, $param);
            }
        }

        return $repository->all();
    }

    /**
     * @param ReflectionMethod $method
     * @param string           $class
     *
     * @return Collection
     */
    private function getAnnotationsFrom(ReflectionMethod $method, string $class): Collection
    {
        $annotations = (new AnnotationReader())->getMethodAnnotations($method);

        return collect($annotations)->filter(fn ($annotation) => is_a($annotation, $class));
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
        ini_set("highlight.comment", "#008000");
        ini_set("highlight.default", "#000000");
        ini_set("highlight.html", "#808080");
        ini_set("highlight.keyword", "#998;");
        ini_set("highlight.string", "#d14");

        $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $code = highlight_string('<?php ' . $json, true);

        $docs = Str::of($code)
            ->replaceFirst('&lt;?php&nbsp;', '')
            ->replace('&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;');

        $keys = $this->arrayKeysMulti($value);

        foreach ($keys as $item) {
            $docs = $docs->replace(
                '<span style="color: #d14">"' . $item . '"</span>',
                '<span style="color: #333">"' . $item . '"</span>'
            );
        }

        return $docs;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function arrayKeysMulti(array $array)
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($array[$key])) {
                $keys = array_merge($keys, $this->arrayKeysMulti($array[$key]));
            }
        }

        return $keys;
    }
}
