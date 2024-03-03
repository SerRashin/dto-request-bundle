<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\Attributes;

use Attribute;
use Ser\DtoRequestBundle\Enum\Source;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Dto
{
    public function __construct()
    {
    }
}
