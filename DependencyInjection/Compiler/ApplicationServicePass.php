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

        // If we're in CLI we need to implement a fake version of the request object
        // since there's no instance of Request available. This could cause an exception
        // at the time of instance an ApplicationService
        if (defined('STDIN')) {
            $requestDefinition = $container->getDefinition('application_service_abstract.request');
            $requestDefinition->setScope('container');
            $requestDefinition->setArgument(0, new Reference('fake_request'));
        }
    }
}
