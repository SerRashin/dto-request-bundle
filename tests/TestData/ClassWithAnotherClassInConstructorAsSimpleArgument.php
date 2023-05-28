<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

class ClassWithAnotherClassInConstructorAsSimpleArgument
{
    private readonly ClassWithStringField $privateClass;

    public function __construct(
        ClassWithStringField $classParameter,
    ) {
        $this->privateClass = $classParameter;
    }

    public function getClass(): ClassWithStringField
    {
        return $this->privateClass;
    }
}
