<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ser_dto_request');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('autowire')
                        ->defaultTrue()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
