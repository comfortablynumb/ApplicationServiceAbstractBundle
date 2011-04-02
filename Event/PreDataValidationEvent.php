<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PreDataValidationEvent extends Event
{
    protected $entity;
    
    public function __construct(ApplicationServiceInterface $service, $entity)
    {
        parent::__construct($service);

        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
