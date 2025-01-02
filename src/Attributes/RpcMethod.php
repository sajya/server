<?php

declare(strict_types=1);

namespace Sajya\Server\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RpcMethod
{
    public function __construct(
        public string|null $description = null,
        public array|null  $params = null,
        public array|null  $result = null
    )
    {
    }
}
