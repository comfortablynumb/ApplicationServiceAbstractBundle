<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PreCreateEvent extends Event
{
    protected $data;
    protected $entity;
    
    public function __construct(ApplicationServiceInterface $service, array $data, $entity)
    {
        parent::__construct($service);

        $this->data = $data;
        $this->entity = $entity;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
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
