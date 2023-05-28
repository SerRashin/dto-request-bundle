<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\Reflection;

use ReflectionAttribute;
use ReflectionParameter;

/**
 * Reflection parameter from class constructor
 */
final class ReflectedParameter implements PropertyInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool
     */
    private bool $isHasAttributes = false;

    /**
     * @var object[]
     */
    private array $attributes = [];

    private bool $hasType;

    /**
     * @var string|null
     */
    private ?string $type = null;

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
    private bool $isNullable;

    /**
     * @var bool
     */
    private bool $isMixed;

    /**
     * @var mixed
     */
    private mixed $defaultValue;

    /**
     * @var ReflectionParameter
     */
    private ReflectionParameter $parameter;

    /**
     * Ctor.
     *
     * @param ReflectionParameter $parameter
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
        $this->name = $parameter->getName();
        $this->hasType = $parameter->hasType();
        $this->types = ReflectedProperty::resolveType($parameter->getType());
        $this->isNullable = $this->resolveAllowsNull($parameter);
        $this->isMixed = $this->resolveIsMixed($parameter);

        $this->defaultValue = null;

        if (count($this->types) !== 0) {
            $this->type = reset($this->types);
        }

        $this->isTypeClassName = false;
        if ($this->type != null && class_exists($this->type)) {
            $this->isTypeClassName = true;
        }

        $this->resolveDefaultValue($parameter);
        $this->resolveAttributes($parameter);
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
     * Is type classname
     *
     * @return bool
     */
    public function isTypeClassName(): bool
    {
        return $this->isTypeClassName;
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
     * @throws \ReflectionException
     */
    private function resolveDefaultValue(ReflectionParameter $parameter)
    {
        try {
            $this->defaultValue = $parameter->getDefaultValue();
        } catch (\Exception $e) {
            if ($this->isNullable === true) {
                return;
            }

            if ($this->defaultValue === null && isset(ReflectedProperty::SCALAR_TYPES[$this->type])) {
                settype($this->defaultValue, $this->type);
            } elseif ($this->type === 'array') {
                $this->defaultValue = [];
            }
        }
    }

    private function resolveAllowsNull(ReflectionParameter $parameter): bool
    {
        if (!$parameter->getType()) {
            return true;
        }

        return $parameter->getType()->allowsNull();
    }

    private function resolveIsMixed(ReflectionParameter $parameter)
    {
        return $parameter->hasType() === false;
    }

    private function resolveAttributes(ReflectionParameter $parameter): void
    {
        $refAttributes = $this->getFilteredProperties($parameter, self::SUPPORTS_ATTRIBUTES);

        foreach ($refAttributes as $attribute) {
            $name = $attribute->getName();
            $this->isHasAttributes = true;
            $this->attributes[$name] = $attribute->newInstance();
        }
    }

    /**
     * @param ReflectionParameter $parameter
     * @param array $filters
     *
     * @return ReflectionAttribute[]
     */
    private function getFilteredProperties(ReflectionParameter $parameter, array $filters = []): iterable
    {
        foreach ($filters as $filter) {
            $attributes = $parameter->getAttributes($filter);

            foreach ($attributes as $attribute) {
                yield $attribute;
            }
        }
    }
}
