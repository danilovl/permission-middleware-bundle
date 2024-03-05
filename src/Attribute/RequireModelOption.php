<?php declare(strict_types=1);

namespace Danilovl\PermissionMiddlewareBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class RequireModelOption
{
    public function __construct(
        public readonly array $requireNames = [],
        public readonly array $optionNames = [],
    ) {}
}
