<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Reflection;

use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Ser\DTORequestBundle\Attributes\MapTo;
use Ser\DTORequestBundle\Attributes\MapToArrayOf;

/**
 * Reflection property from class
 */
final class ReflectedProperty
{
    public const SUPPORTS_ATTRIBUTES = [MapTo::class, MapToArrayOf::class];

    /**
     * @var bool
     */
    private bool $isHasAttributes = false;

    /**
     * @var object[]
     */
    private array $attributes = [];

    /**
     * @var bool
     */
    private bool $hasType;

    /**
     * @var string|null
     */
    private ?string $type;

    /**
     * @var bool
     */
    private bool $isTypeClassName;

    /**
     * @var string[]
     */
    private array $types;

    /**
     * @var bool
     */
    private bool $hasDefaultValue;

    /**
     * @var bool
     */
    private bool $isNullable;

    /**
     * @var bool
     */
    private bool $isMixed;

    /**
     * @var bool
     */
    private bool $isReadOnly;

    /**
     * @var mixed
     */
    private mixed $defaultValue;

    /**
     * @var ReflectionProperty
     */
    private ReflectionProperty $property;

    /**
     * Ctor.
     *
     * @param ReflectionProperty $property
     */
    public function __construct(ReflectionProperty $property)
    {
        $this->property = $property;
        $this->hasType = $property->hasType();
        $this->hasDefaultValue = $property->isDefault();
        $this->isNullable = $this->resolveAllowsNull($property);
        $this->isMixed = $this->resolveIsMixed($property);
        $this->types = $this->resolveType($property->getType());
        $this->isReadOnly = $property->isReadOnly();

        $this->type = null;
        if (count($this->types) !== 0) {
            $this->type = reset($this->types);
        }

        $this->isTypeClassName = false;
        if ($this->type != null && class_exists($this->type)) {
            $this->isTypeClassName = true;
        }

        $this->resolveDefaultValue($property);

        $this->resolveAttributes($property);
    }

    /**
     * Is property has any type
     *
     * @return bool
     */
    public function hasType(): bool
    {
        return $this->hasType;
    }

    /**
     * Returns first type of array (if declared a union type)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Is type classname
     *
     * @return bool
     */
    public function isTypeClassName(): bool
    {
        return $this->isTypeClassName;
    }

    /**
     * Check is type exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isTypeExists(string $name): bool
    {
        return in_array($name, $this->types);
    }

    /**
     * Get types
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Is property has default value
     *
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * Is property supports null value
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * Is property supports any types
     *
     * @return bool
     */
    public function isMixed(): bool
    {
        return $this->isMixed;
    }

    /**
     * Is property has attributes
     *
     * @return bool
     */
    public function isHasAttributes(): bool
    {
        return $this->isHasAttributes;
    }

    /**
     * Get attributes list
     *
     * @return object[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get attribute
     *
     * @param string $name
     *
     * @return object|null
     */
    public function getAttribute(string $name): ?object
    {
        if (!isset($this->attributes[$name])) {
            return null;
        }

        return $this->attributes[$name];
    }

    /**
     * Is readonly property
     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * Get default value for property
     *
     * Returns default value if set, or value from type.
     *
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Set property
     *
     * @param $objectOrValue
     * @param $value
     *
     * @return void
     */
    public function setValue($objectOrValue, $value = null): void
    {
         $this->property->setValue($objectOrValue, $value);
    }

    private function resolveAllowsNull(ReflectionProperty $property): bool
    {
        if (! $property->getType()) {
            return true;
        }

        return $property->getType()->allowsNull();
    }

    private function resolveIsMixed(ReflectionProperty $property): bool
    {
        return $property->hasType() === false;
    }

    private function resolveDefaultValue(ReflectionProperty $property)
    {
        $this->defaultValue = $property->getDefaultValue();
        if ($this->defaultValue === null && $this->isNullable === false) {
            settype($this->defaultValue, $this->type);
        }
    }

    /**
     * @param ReflectionProperty $property
     * @param array $filters
     *
     * @return ReflectionAttribute[]
     */
    private function getFilteredProperties(ReflectionProperty $property, array $filters = []): iterable
    {
        foreach ($filters as $filter) {
            $attributes = $property->getAttributes($filter);

            foreach ($attributes as $attribute) {
                yield $attribute;
            }
        }
    }

    private function resolveAttributes(ReflectionProperty $property): void
    {
        $refAttributes = $this->getFilteredProperties($property, self::SUPPORTS_ATTRIBUTES);

        foreach ($refAttributes as $attribute) {
            $name = $attribute->getName();
            $this->isHasAttributes = true;
            $this->attributes[$name] = $attribute->newInstance();
        }
    }

    public static function resolveType(?ReflectionType $refType)
    {
        if ($refType === null) {
            return [];
        }

        switch (true) {
            case $refType instanceof ReflectionNamedType:
                $types[] = $refType->getName();
                break;
            case $refType instanceof ReflectionUnionType:
                foreach ($refType->getTypes() as $type) {
                    $types[] = $type->getName();
                }
                break;
        }

        return $types;
    }
}
