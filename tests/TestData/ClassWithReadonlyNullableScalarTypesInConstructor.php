<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

class ClassWithReadonlyNullableScalarTypesInConstructor
{
    public function __construct(
        public readonly ?int $intField,
        public readonly ?bool $boolField,
        public readonly ?float $floatField,
        public readonly ?string $stringField,
    ) {
    }
}
