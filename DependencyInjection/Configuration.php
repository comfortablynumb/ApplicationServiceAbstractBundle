<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;

class Configuration implements ConfigurationInterface
{
    const APPLICATION_SERVICE_CONCURRENCY_LOCK_DEFAULT  = 'none';
    const APPLICATION_SERVICE_REQUEST_CLASS_DEFAULT     = 'ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestBase';
    const APPLICATION_SERVICE_RESPONSE_CLASS_DEFAULT    = 'ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseArray';
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('application_service_abstract');

        $rootNode
            ->children()
                ->scalarNode('concurrency_lock_type')
                    ->defaultValue(self::APPLICATION_SERVICE_CONCURRENCY_LOCK_DEFAULT)
                    ->validate()
                        ->ifNotInArray(array(
                            'none',
                            'optimistic',
                            'pessimistic_read',
                            'pessimistic_write'
                        ))
                        ->thenInvalid('Concurrency Lock type "%s" is not valid')
                    ->end()
                ->end()
            
            ->scalarNode('base_class')
            ->end()
            
            ->arrayNode('request')
                ->canBeUnset()
                ->children()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
            
            ->arrayNode('response')
                ->canBeUnset()
                ->children()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
            
            ->arrayNode('acl_manager')
                ->canBeUnset()
                ->children()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
            
            ->arrayNode('validation_errors_formatter')
                ->canBeUnset()
                ->children()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
            
            ->arrayNode('event_dispatcher')
                ->canBeUnset()
                ->children()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}