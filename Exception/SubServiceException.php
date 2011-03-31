<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseInterface;

class SubServiceException extends \Exception implements ApplicationServiceExceptionInterface
{
    protected $subServiceResponse = null;
    
    
    
    public function __construct( $message = '', $code = 0, \Exception $previous = null, ApplicationServiceResponseInterface $response )
    {
        $this->setSubServiceResponse( $response );
        
        parent::__construct( $message, $code, $previous );
    }
    
    public function getType()
    {
        return 'SubServiceException';
    }
    
    public function setSubServiceResponse( ApplicationServiceResponseInterface $response )
    {
        $this->subServiceResponse = $response;
    }
    
    public function getSubServiceResponse()
    {
        return $this->subServiceResponse;
    }
}
