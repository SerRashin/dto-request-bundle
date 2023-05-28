<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle;

use Ser\DtoRequestBundle\ValueResolver\RequestToDtoObjectResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Data Transfer Object Bundle
 */
class SerDtoRequestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $definition = new Definition(DataTransferObjectFactory::class);
        $container->setDefinition('dto_request.factory', $definition);

        $definition = new Definition(RequestToDtoObjectResolver::class, [
            new Reference('dto_request.factory')
        ]);

        $definition->addTag('controller.argument_value_resolver');
        $container->setDefinition('dto_request.resolver', $definition);
    }
}
