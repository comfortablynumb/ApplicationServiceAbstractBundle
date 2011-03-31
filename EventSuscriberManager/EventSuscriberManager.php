<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\EventSuscriberManager;

use Symfony\Component\EventDispatcher\EventDispatcher;

use ENC\Bundle\ApplicationServiceAbstractBundle\EventSuscriber\EventSuscriberInterface;

class EventSuscriberManager
{
    protected $dispatcher;
    
    public function __construct( EventDispatcher $dispatcher )
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function register( EventSuscriberInterface $eventSuscriber )
    {
        $dispatcher = $this->getDispatcher();
        $events     = $eventSuscriber->getSuscribedEvents();
		
		if ( !is_array( $events ) )
		{
			throw new \InvalidArgumentException( sprintf( 'La clase "%s" debe devolver del método "%s" un array de eventos de la clase "%s"',
				get_class( $suscriber ),
				'getSuscribedEvents',
				'ApplicationService' ) );
		}        
        
        foreach ( $events as $event => $method )
        {
            $dispatcher->connect( $event, array( $eventSuscriber, $method ) );
        }
    }
    
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}