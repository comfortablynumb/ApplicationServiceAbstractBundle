<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ApplicationServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Get Suscribers
        $subscribersIDs = array_keys( $container->findTaggedServiceIds('application_service_abstract.event_subscriber'));
        $eventDispatcherDefinition = $container->getDefinition('application_service_abstract.event_dispatcher');
            
        foreach ($subscribersIDs as $subscriberID) {
            $eventDispatcherDefinition->addMethodCall('addSubscriber', array(new Reference($subscriberID)));
        }
    }
}
