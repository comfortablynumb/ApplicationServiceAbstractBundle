<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PreFindEvent extends Event
{
    protected $data;
    protected $qb;
    
    public function __construct(ApplicationServiceInterface $service, array $data, $qb = null)
    {
        parent::__construct($service);

        $this->data = $data;
        $this->qb = $qb;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getQueryBuilder()
    {
        return $this->qb;
    }

    public function setQueryBuilder($qb)
    {
        $this->qb = $qb;
    }
}
