<?php

declare(strict_types=1);

namespace Sajya\Server\Annotations;

/**
 * @Annotation
 */
final class Param
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $value;
}
