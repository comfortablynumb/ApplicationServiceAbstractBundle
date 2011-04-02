<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PostDeleteEvent extends Event
{
    protected $id;
    protected $entity;
    
    public function __construct(ApplicationServiceInterface $service, $id, $entity)
    {
        parent::__construct($service);

        $this->id = $id;
        $this->entity = $entity;
    }

    public function getID()
    {
        return $this->id;
    }

    public function setID( $id )
    {
        $this->id = $id;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity( $entity )
    {
        $this->entity = $entity;
    }
}
