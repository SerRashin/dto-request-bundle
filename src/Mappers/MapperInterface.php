<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\Mappers;

use Ser\DTORequestBundle\Exceptions\RequiredDataException;

interface MapperInterface
{
    /**
     * Create mapped property
     *
     * @param mixed  $data     Data for casting
     * @param string $typeName Casting typename
     *
     * @return array|object|null
     *
     * @throws RequiredDataException
     */
    public function create(mixed $data, string $typeName): array|object|null;
}
