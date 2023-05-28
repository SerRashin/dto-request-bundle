<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

class ClassWithNullableScalarTypes
{
    public ?int $intField;
    public ?bool $boolField;
    public ?float $floatField;
    public ?string $stringField;
}
