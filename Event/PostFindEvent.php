<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PostFindEvent extends Event
{
    protected $data;
    protected $results;
    
    public function __construct(ApplicationServiceInterface $service, array $data, array $results)
    {
        parent::__construct($service);

        $this->data = $data;
        $this->results = $results;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;
    }
}
