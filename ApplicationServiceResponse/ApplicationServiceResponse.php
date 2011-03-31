<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseInterface;

abstract class ApplicationServiceResponse implements ApplicationServiceResponseInterface
{
    protected $data         = array();
    protected $rowObject    = null;
    
    public function __construct( array $data = array() )
    {
        $this->setData( $data );
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function setData( array $data )
    {
        $this->data = $data;
    }
}
