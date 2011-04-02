<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PreUpdateEvent extends Event
{
    protected $data;
    protected $entity;
    
    public function __construct(ApplicationServiceInterface $service, array $data, $entity)
    {
        parent::__construct($service);

        $this->data = $data;
        $this->entity = $entity;
    }
}
