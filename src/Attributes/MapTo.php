<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapTo
{
    public function __construct(
        public string $className,
    ) {
    }
}
