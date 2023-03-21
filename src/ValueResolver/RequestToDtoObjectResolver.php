<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle\ValueResolver;

use Exception;
use Ser\DTORequestBundle\DataTransferObjectFactoryInterface;
use Ser\DTORequestBundle\Exceptions\NullablePropertyException;
use Ser\DTORequestBundle\Exceptions\RequiredDataException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestToDtoObjectResolver implements ValueResolverInterface
{
    public function __construct(
        private DataTransferObjectFactoryInterface $dataTransferObjectFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !class_exists($argumentType)) {
            return [];
        }

        $requestData = $request->toArray();

        if (count($requestData) === 0) {
            return [];
        }

        if (!$this->dataTransferObjectFactory->supports($argumentType)) {
            return [];
        }

        try {
            $object = $this->dataTransferObjectFactory->create($requestData, $argumentType);
        } catch (NullablePropertyException | RequiredDataException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid request is passed', $e, $e->getCode());
        }

        return [$object];
    }
}
