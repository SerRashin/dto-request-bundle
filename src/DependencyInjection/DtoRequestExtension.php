<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\DependencyInjection;

use Ser\DtoRequestBundle\DataTransferObjectFactory;
use Ser\DtoRequestBundle\DataTransferObjectFactoryInterface;
use Ser\DtoRequestBundle\ValueResolver\RequestToDtoObjectResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Reference;

class DtoRequestExtension extends ConfigurableExtension
{
    public function loadInternal(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->register(DataTransferObjectFactory::class)
            ->setPublic(false)
        ;

        $container->setAlias(
            DataTransferObjectFactoryInterface::class,
            DataTransferObjectFactory::class
        )->setPublic(false);

        $container->register(RequestToDtoObjectResolver::class)
            ->setArguments([
                '$dataTransferObjectFactory' =>  new Reference(DataTransferObjectFactoryInterface::class),
                '$autowire' => $config['autowire'],
            ])
            ->addTag('controller.argument_value_resolver', ['priority' => 40])
            ->setPublic(false)
        ;
    }
}
