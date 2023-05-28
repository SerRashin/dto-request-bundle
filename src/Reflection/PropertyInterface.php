<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\Reflection;

use Ser\DtoRequestBundle\Attributes\MapTo;
use Ser\DtoRequestBundle\Attributes\MapToArrayOf;

interface PropertyInterface
{
    public const SUPPORTS_ATTRIBUTES = [MapTo::class, MapToArrayOf::class];
    public const SCALAR_TYPES = [
        "int"    => true,
        "bool"   => true,
        "float"  => true,
        "string" => true,
        "double" => true,
    ];

    /**
     * Get property name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Is property has any type
     *
     * @return bool
     */
    public function hasType(): bool;

    /**
     * Returns first type of array (if declared a union type)
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Get attributes list
     *
     * @return object[]
     */
    public function getAttributes(): array;
}
