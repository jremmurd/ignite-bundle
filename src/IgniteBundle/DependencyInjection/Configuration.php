<?php

namespace JRemmurd\IgniteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ignite');

        $rootNode
            ->children()
                ->arrayNode('drivers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('service_id')->isRequired()->end()
                            ->arrayNode('config')
                                ->children()
                                    ->scalarNode("key")->end()
                                    ->scalarNode("secret")->end()
                                    ->scalarNode("id")->end()
                                    ->booleanNode("log_to_console")->end()
                                    ->arrayNode("options")
                                        ->children()
                                            ->scalarNode("cluster")->end()
                                            ->booleanNode("forceTLS")->end()
                                            ->arrayNode('auth')
                                                ->children()
                                                    ->arrayNode("params")
                                                        ->useAttributeAsKey('name')
                                                        ->prototype('scalar')->end()
                                                    ->end()
                                                    ->arrayNode("headers")
                                                        ->useAttributeAsKey('name')
                                                        ->prototype('scalar')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('channels')
                    ->children()
                        ->scalarNode('strict_parameters')->defaultTrue()->info("Do not allow to create channels with parameters which are not configured.")->end()
                        ->scalarNode("default_driver_name")->defaultValue("logger")->end()
                        ->scalarNode("factory_id")->defaultValue("ignite.channel_factory")->end()
                        ->arrayNode('namespaces')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode("default_driver_name")->end()
                                    ->scalarNode('pattern')->end()
                                    ->scalarNode('authEndpoint')->isRequired()->end()
                                    ->arrayNode('channels')
                                        ->children()
                                            ->arrayNode("presence")
                                            ->useAttributeAsKey("name")
                                                ->prototype('array')
                                                    ->children()
                                                        ->scalarNode("name")->end()
                                                        ->scalarNode("authenticator")->end()
                                                        ->booleanNode("useSlugForJS")->end()
                                                        ->arrayNode("drivers")->prototype('scalar')->end()->end()
                                                        ->arrayNode("parameters")->prototype('scalar')->end()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode("private")
                                            ->useAttributeAsKey("name")
                                                ->prototype('array')
                                                    ->children()
                                                        ->scalarNode("name")->end()
                                                        ->scalarNode("authenticator")->end()
                                                        ->booleanNode("useSlugForJS")->end()
                                                        ->arrayNode("drivers")->prototype('scalar')->end()->end()
                                                        ->arrayNode("parameters")->prototype('scalar')->end()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode("public")
                                            ->useAttributeAsKey("name")
                                                ->prototype('array')
                                                    ->children()
                                                        ->scalarNode("name")->end()
                                                        ->arrayNode("drivers")->prototype('scalar')->end()->end()
                                                        ->booleanNode("useSlugForJS")->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
