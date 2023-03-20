<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Reflected class
 */
final class ReflectedClass
{
    /**
     * @var ReflectedProperty[]
     */
    private array $properties;

    /**
     * @var ReflectedParameter[]
     */
    private array $parameters = [];

    /**
     * @var string[]
     */
    private array $parametersValues = [];

    /**
     * @var string[]
     */
    private array $propertiesValues = [];

    /**
     * Ctor.
     *
     * @param string $className
     *
     * @throws ReflectionException
     */
    public function __construct(string $className)
    {
        $refClass = new ReflectionClass($className);

        foreach ($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $field = $property->getName();

            $refProperty =  new ReflectedProperty($property);

            $this->properties[$field] = $refProperty;

            if ($refProperty->isNullable()) {
                $this->propertiesValues[$field] = null;
            } else {
                $this->propertiesValues[$field] = filter_var($field);
            }
        }

        $constructor = $refClass->getConstructor();

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $parameter) {
                $field = $parameter->getName();

                $this->parameters[$field] = new ReflectedParameter($parameter);
                $this->parametersValues[$field] = null;
            }
        }
    }

    /**
     * Check is property exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isPropertyExists(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * Get class property by name
     *
     * @param string $name
     *
     * @return ReflectedProperty|null
     */
    public function getProperty(string $name): ?ReflectedProperty
    {
        if (!$this->isPropertyExists($name)) {
            return null;
        }

        return $this->properties[$name];
    }

    /**
     * Get class properties
     *
     * @return ReflectedProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Check is parameter exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isParameterExists(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Get class constructor parameter by name
     *
     * @param string $name
     *
     * @return ReflectedParameter|null
     */
    public function getParameter(string $name): ?ReflectedParameter
    {
        if (!$this->isParameterExists($name)) {
            return null;
        }

        return $this->parameters[$name];
    }

    /**
     * Get class constructor parameters
     *
     * @return ReflectedParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string[]
     */
    public function getParametersValues(): array
    {
        return $this->parametersValues;
    }

    /**
     * @return string[]
     */
    public function getPropertiesValues(): array
    {
        return $this->propertiesValues;
    }
}
