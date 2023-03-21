<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Reflection;

use ReflectionParameter;

/**
 * Reflection parameter from class constructor
 */
final class ReflectedParameter
{
    private bool $hasType;

    /**
     * @var string|null
     */
    private ?string $type = null;

    /**
     * @var string[]
     */
    private array $types;

    /**
     * Ctor.
     *
     * @param ReflectionParameter $parameter
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->hasType = $parameter->hasType();
        $this->types = ReflectedProperty::resolveType($parameter->getType());

        if (count($this->types) !== 0) {
            $this->type = reset($this->types);
        }
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
}
