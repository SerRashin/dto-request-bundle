<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle;

use ReflectionException;
use Ser\DTORequestBundle\Exceptions\NullablePropertyException;
use Ser\DTORequestBundle\Exceptions\RequiredDataException;

/**
 * Interface for DTO factories
 */
interface DataTransferObjectFactoryInterface
{
    /**
     * Create dto
     *
     * @param array $data       object data
     * @param string $className object className
     *
     * @return object
     *
     * @throws RequiredDataException
     * @throws ReflectionException
     * @throws NullablePropertyException
     */
    public function create(array $data, string $className): object;

    /**
     * Check is class supports transform to DTO
     *
     * @param string $className
     *
     * @return mixed
     */
    public function supports(string $className): bool;
}
