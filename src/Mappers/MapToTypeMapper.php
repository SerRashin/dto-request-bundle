<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Mappers;

use Ser\DTORequestBundle\DataTransferObjectFactoryInterface;

class MapToTypeMapper implements MapperInterface
{
    public function __construct(
        private DataTransferObjectFactoryInterface $dataTransferObjectFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(mixed $data, string $typeName): array|object|null
    {
        return $this->dataTransferObjectFactory->create($data, $typeName);
    }
}
