<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MapTo
{
    public function __construct(
        public string $className,
    ) {
    }
}
