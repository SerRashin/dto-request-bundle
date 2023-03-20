<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle;

use ReflectionException;
use Ser\DTORequestBundle\Attributes\MapTo;
use Ser\DTORequestBundle\Attributes\MapToArrayOf;
use Ser\DTORequestBundle\Exceptions\RequiredDataException;
use Ser\DTORequestBundle\Reflection\ReflectedClass;
use Ser\DTORequestBundle\Reflection\ReflectedProperty;

/**
 * Data transfer object factory
 */
class DataTransferObjectFactory implements DataTransferObjectFactoryInterface
{
    private array $scalarTypes = [
        "int" => true,
        "bool" => true,
        "float" => true,
        "string" => true,
        "double" => true,
    ];

    /**
     * @inheritDoc
     */
    public function create(array $data, string $className): object
    {
        $existsKeys = array_flip(array_keys($data));
        $cachedDto = $this->getCachedDto($className);

        $parameters = $cachedDto->getParameters();
        $properties = $cachedDto->getProperties();

        $constructorArguments = [];

        foreach ($properties as $field => $property) {
            if (!isset($existsKeys[$field])) {
                continue;
            }

            $type = $property->getType();
            $isMixed = $property->isMixed();
            $value = $data[$field];

            if (is_scalar($value) && (isset($this->scalarTypes[$type]) || $isMixed)) {
                continue;
            }

            $isValueArray = is_array($value);

            if ($property->isHasAttributes()) {
                foreach ($property->getAttributes() as $mapTo => $attribute) {
//                    if (!property_exists($attribute, 'className'))
//                        continue;

                    $targetType = $attribute->className;

                    if (!in_array($mapTo, ReflectedProperty::SUPPORTS_ATTRIBUTES)) {
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
                if (!$isValueArray) {
                    $data[$field] = new $type($value);
                } else {
                    $data[$field] = $this->create($value, $type);
                }
            }
        }

        $constructorKeys = [];

        if (!empty($parameters)) {
            foreach ($parameters as $field => $property) {
                if (!isset($data[$field])) {
                    throw new RequiredDataException($field);
                }
                $constructorKeys[$field] = true;
                $constructorArguments[] = $data[$field];
            }
        }

        $object = new $className(...$constructorArguments);

        if (!empty($properties)) {
            foreach ($properties as $field => $property) {
                if (isset($constructorKeys[$field])) {
                    continue;
                }

                $value = isset($existsKeys[$field]) === true ? $data[$field] : $property->getDefaultValue();

                if ($property->isReadOnly()) {
                    $property->setValue($object, $value);
                } else {
                    $object->{$field} = $value;
                }
            }
        }

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $className): bool
    {
        $classVars = get_class_vars($className);

        if (count($classVars) === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $className
     * @return ReflectedClass
     *
     * @throws ReflectionException
     */
    private function getCachedDto(string $className): ReflectedClass
    {
        return DTOCache::resolve($className, function (string $className) {
            return new ReflectedClass($className);
        });
    }
}
