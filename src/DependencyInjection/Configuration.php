<?php

namespace Hgabka\PayUBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /** @var ContainerBuilder */
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hgabka_pay_u');

        $rootNode
            ->children()
                ->scalarNode('merchant')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('logging')->defaultTrue()->end()
                ->booleanNode('migration')->defaultTrue()->end()
                ->booleanNode('sandbox')->defaultFalse()->end()
                ->scalarNode('log_path')->defaultValue($this->container->getParameter('kernel.logs_dir').'/payu')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
