<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ApplicationServicePass implements CompilerPassInterface
{
	public function process( ContainerBuilder $container )
	{
		if ( $container->hasDefinition( 'application_service_abstract.event_suscriber_manager' ) )
		{
			// Get Suscribers
			$suscribersIDs               = array_keys( $container->findTaggedServiceIds( 'application_service_abstract.event_suscriber' ) );
			$suscriberManagerDefinition  = $container->getDefinition( 'application_service_abstract.event_suscriber_manager' );
            
			foreach ( $suscribersIDs as $suscriberID )
			{
                $suscriberManagerDefinition->addMethodCall( 'register', array( new Reference( $suscriberID ) ) );
			}
		}
	}
}
