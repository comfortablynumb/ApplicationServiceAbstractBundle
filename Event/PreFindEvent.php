<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class PreFindEvent extends Event
{
    protected $data;
    
    public function __construct( ApplicationServiceInterface $service, array $data )
    {
        parent::__construct( $service );

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}
