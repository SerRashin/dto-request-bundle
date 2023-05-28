<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\ValueResolver;

use Exception;
use Ser\DtoRequestBundle\DataTransferObjectFactoryInterface;
use Ser\DtoRequestBundle\Exceptions\NullablePropertyException;
use Ser\DtoRequestBundle\Exceptions\RequiredPropertyException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Ser\DtoRequestBundle\Attributes\Dto;
use Ser\DtoRequestBundle\DtoInterface;

class RequestToDtoObjectResolver implements ValueResolverInterface
{
    public function __construct(
        private DataTransferObjectFactoryInterface $dataTransferObjectFactory,
        private readonly bool $autowire
    ) {
    }

    public function checkIsSupports(ArgumentMetadata $argument)
    {
        if ($this->autowire) {
            return true;
        }

        $attribute = $argument->getAttributesOfType(Dto::class, ArgumentMetadata::IS_INSTANCEOF)[0]
            ?? $argument->getAttributesOfType(Dto::class, ArgumentMetadata::IS_INSTANCEOF)[0]
            ??  null;

        if ($attribute !== null) {
            return true;
        }

        if (is_subclass_of($argument->getType(), DtoInterface::class)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $argumentType = $argument->getType();

        $isSupports = $this->checkIsSupports($argument);

        if (!$isSupports) {
            return [];
        }

        if (!$argumentType || !class_exists($argumentType)) {
            return [];
        }

        if ($request->getContent() === '') {
            return [];
        }

        $requestData = $request->toArray();

        if (count($requestData) === 0) {
            return [];
        }

        try {
            $object = $this->dataTransferObjectFactory->create($requestData, $argumentType);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid request is passed', $e, $e->getCode());
        }

        return [$object];
    }
}
