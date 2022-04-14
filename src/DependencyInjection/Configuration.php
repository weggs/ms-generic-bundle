<?php

namespace Weggs\GenericBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('weggs_generic');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('abstract_request')
                    ->children()
                        ->scalarNode('keycloak_base_url')
                            ->defaultValue('%env(KEYCLOAK_BASE_URL)%')
                        ->end()
                        ->scalarNode('keycloak_client_id')
                            ->defaultValue('%env(KEYCLOAK_CLIENT_ID)%')
                        ->end()
                        ->scalarNode('keycloak_client_secret')
                            ->defaultValue('%env(KEYCLOAK_CLIENT_SECRET)%')
                        ->end()
                        ->scalarNode('redis_url')
                            ->defaultValue('%env(REDIS_URL)%')
                        ->end()
                        ->scalarNode('redis_token_key')
                            ->defaultValue('microservice.keycloak.token')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
