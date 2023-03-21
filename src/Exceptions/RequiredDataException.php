<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Exceptions;

use Exception;

class RequiredDataException extends Exception
{
    public function __construct(string $property)
    {
        parent::__construct(
            "Request not contains required property `$property`"
        );
    }
}
