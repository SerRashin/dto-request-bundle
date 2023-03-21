<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Exceptions;

use Exception;

class RequiredPropertyException extends Exception
{
    public function __construct(string $property)
    {
        parent::__construct(
            "Request not contains required property `$property`"
        );
    }
}
