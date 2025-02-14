<?php

declare(strict_types=1);

namespace Sajya\Server\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RpcMethod
{
    public function __construct(
        public ?string $description = null,
        public ?array $params = null,
        public ?array $result = null
    ) {}
}
