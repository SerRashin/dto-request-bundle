<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Exceptions;

use Exception;

class NullablePropertyException extends Exception
{
    public function __construct(string $propertyName, string $typeName)
    {
        parent::__construct(
            "Property `$propertyName` cant be null. Change you dto type to `?$typeName`"
        );
    }
}
