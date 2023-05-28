<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle;

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
     */
    public function create(array $data, string $className): object;
}
