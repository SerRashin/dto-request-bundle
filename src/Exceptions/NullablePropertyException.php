<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Exceptions;

use Exception;

class NullablePropertyException extends Exception
{
    public function __construct(string $propertyName, string $typeName)
    {
        parent::__construct(
            "Property $propertyName can't be null. Change your dto type to ?$typeName"
        );
    }
}
