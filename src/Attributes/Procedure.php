<?php

declare(strict_types=1);

namespace Sajya\Server\Attributes;

use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Procedure
{
    public function __construct(
        public string $name
    ) {}

    public static function name(string $class): string
    {
        $attributes = (new ReflectionClass($class))->getAttributes(self::class);

        abort_unless($attributes !== [], 500, "Class '$class' must have the ".self::class.' attribute');

        return $attributes[0]->newInstance()->name;
    }
}
