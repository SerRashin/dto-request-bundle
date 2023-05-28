<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

use Ser\DtoRequestBundle\Attributes\MapToArrayOf;

class ClassWithAnotherClassThatContainsArrayMapper
{
    #[MapToArrayOf(ClassWithStringField::class)]
    public array $array;
}
