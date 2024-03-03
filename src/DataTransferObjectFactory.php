<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle;

use ReflectionException;
use Ser\DtoRequestBundle\Attributes\MapTo;
use Ser\DtoRequestBundle\Attributes\MapToArrayOf;
use Ser\DtoRequestBundle\Reflection\PropertyInterface;
use Ser\DtoRequestBundle\Reflection\ReflectedClass;
use Ser\DtoRequestBundle\Reflection\ReflectedParameter;
use Ser\DtoRequestBundle\Reflection\ReflectedProperty;

class DataTransferObjectFactory implements DataTransferObjectFactoryInterface
{
    public function create(mixed $data, string $targetType): object
    {
        $existsKeys = array_flip(array_keys($data));
        $cachedDto = $this->getCachedDto($targetType);

        $parameters = $cachedDto->getParameters();
        $properties = $cachedDto->getProperties();

        $constructorArguments = [];

        foreach ($properties as $field => $property) {
            if (!isset($existsKeys[$field])) {
                continue;
            }

            // if field in properties and parameters ignore
            if ($cachedDto->hasParameter($field)) {
                continue;
            }

            $this->castPropertyToData($property, $data, $field);
        }

        foreach ($parameters as $field => $parameter) {
            if (!isset($existsKeys[$field])) {
                continue;
            }

            $this->castPropertyToData($parameter, $data, $field);
        }

        if (!empty($parameters)) {
            foreach ($parameters as $field => $parameter) {
                $constructorKeys[$field] = true;

                $value = $data[$field] ?? $parameter->getDefaultValue();

                // if type is classname and value is null
                if (
                    $parameter->isTypeClassName() &&
                    !$parameter->isNullable() &&
                    $value === null
                ) {
                    $value = $this->create([], $parameter->getType());
                }

                if (
                    $parameter->hasAttributes() &&
                    !$parameter->isNullable() &&
                    $value === null
                ) {
                    $value = $this->getDefaultValueFromMappers($parameter);
                }

                $constructorArguments[] = $value;
            }
        }

        $object = new $targetType(...$constructorArguments);

        if (!empty($properties)) {
            foreach ($properties as $field => $property) {
                if (isset($constructorKeys[$field])) {
                    continue;
                }

                $value = $data[$field] ?? $property->getDefaultValue();

                // if type is classname and value is null
                if (
                    $property->isTypeClassName() &&
                    !$property->isNullable() &&
                    $value === null
                ) {
                    $value = $this->create([], $property->getType());
                }

                if (
                    $property->hasAttributes() &&
                    !$property->isNullable() &&
                    $value === null
                ) {
                    $value = $this->getDefaultValueFromMappers($property);
                }

                $this->setObjectValue($object, $property, $value);
            }
        }

        return $object;
    }

    private function getDefaultValueFromMappers(PropertyInterface $property): mixed
    {
        foreach ($property->getAttributes() as $mapTo => $attribute) {
            $targetType = $attribute->className;

            if (!in_array($mapTo, PropertyInterface::SUPPORTS_ATTRIBUTES)) {
                continue;
            }

            switch ($mapTo) {
                case MapToArrayOf::class:
                    return [];
                case MapTo::class:
                    return $this->create([], $targetType);
            }
        }

        return null;
    }

    private function castPropertyToData(
        ReflectedProperty|ReflectedParameter $property,
        array &$data,
        string $field
    ) {
        $type = $property->getType();
        $isMixed = $property->isMixed();
        $value = $data[$field];

        if (is_scalar($value) && (isset(ReflectedProperty::SCALAR_TYPES[$type]) || $isMixed)) {
            return;
        }

        $isValueArray = is_array($value);

        if ($property->hasAttributes()) {
            foreach ($property->getAttributes() as $mapTo => $attribute) {
                $targetType = $attribute->className;

                if (!in_array($mapTo, PropertyInterface::SUPPORTS_ATTRIBUTES)) {
                    continue;
                }

                switch ($mapTo) {
                    case MapToArrayOf::class:
                        foreach ($value as $key => $val) {
                            if (!is_array($val)) {
                                $data[$field][$key] = new $targetType($val);
                            } else {
                                $data[$field][$key] = $this->create($val, $targetType);
                            }
                        }
                        break;
                    case MapTo::class:
                        if (!$isValueArray) {
                            $data[$field] = new $targetType($value);
                        } else {
                            $data[$field] = $this->create($value, $targetType);
                        }
                        break;
                }
            }
        }

        if ($property->isTypeClassName() === true) {
            if ($value === null) {
                return;
            }

            if (!$isValueArray) {
                $data[$field] = new $type($value);
            } else {
                $data[$field] = $this->create($value, $type);
            }
        }
    }

    private function setObjectValue(object &$object, ReflectedProperty|ReflectedParameter $property, mixed $value)
    {
        // if property is readonly, set property from reflection
        if ($property->isReadOnly()) {
            $property->setValue($object, $value);
        } else {
            $object->{$property->getName()} = $value;
        }
    }

    /**
     * @param string $className
     *
     * @return ReflectedClass
     *
     * @throws ReflectionException
     */
    private function getCachedDto(string $className): ReflectedClass
    {
        return DtoCache::resolve($className, function (string $className) {
            return new ReflectedClass($className);
        });
    }
}
