<?php

declare(strict_types=1);

namespace Sajya\Server\Annotations;

/**
 * @Annotation
 */
final class Result
{
    public string $name;

    /**
     * @var string
     */
    public $value;
}
