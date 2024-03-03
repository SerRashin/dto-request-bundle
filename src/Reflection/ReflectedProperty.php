<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\Reflection;

use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

/**
 * Reflection property from class
 */
final class ReflectedProperty implements PropertyInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool
     */
    private bool $hasAttributes = false;

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
     * @var bool
     */
    private bool $isTypeInterfaceName;

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
        $this->name = $property->getName();
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
        $this->isTypeInterfaceName = false;

        if ($this->type != null && class_exists($this->type)) {
            $this->isTypeClassName = true;
        } elseif ($this->type != null && interface_exists($this->type)) {
            $this->isTypeInterfaceName = true;
        }

        $this->resolveDefaultValue($property);

        $this->resolveAttributes($property);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function hasType(): bool
    {
        return $this->hasType;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Is type class name
     *
     * @return bool
     */
    public function isTypeClassName(): bool
    {
        return $this->isTypeClassName;
    }

    /**
     * Is type interface name
     *
     * @return bool
     */
    public function isTypeInterfaceName(): bool
    {
        return $this->isTypeInterfaceName;
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
    public function hasAttributes(): bool
    {
        return $this->hasAttributes;
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

        if ($this->isNullable === true) {
            return;
        }

        if ($this->defaultValue === null && isset(self::SCALAR_TYPES[$this->type])) {
            settype($this->defaultValue, $this->type);
        } elseif ($this->type === 'array') {
            $this->defaultValue = [];
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
            $this->hasAttributes = true;
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
