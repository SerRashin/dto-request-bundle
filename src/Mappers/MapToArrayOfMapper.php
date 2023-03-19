<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Mappers;

use Ser\DTORequestBundle\DataTransferObjectFactoryInterface;

class MapToArrayOfMapper implements MapperInterface
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
        foreach ($data as $key => $value) {
            $data[$key] = $this->dataTransferObjectFactory->create($value, $typeName);
        }

        return $data;
    }
}
