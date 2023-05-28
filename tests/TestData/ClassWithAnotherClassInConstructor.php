<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

class ClassWithAnotherClassInConstructor
{
    public function __construct(
        public readonly ClassWithStringField $stringFieldClass,
    ) {
    }
}
