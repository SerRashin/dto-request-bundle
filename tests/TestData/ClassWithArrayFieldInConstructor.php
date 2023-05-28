<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

class ClassWithArrayFieldInConstructor
{
    public function __construct(
        public array $arrayField,
    ) {

    }
}
