<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest;

use Symfony\Component\HttpFoundation\Request;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;

abstract class ApplicationServiceRequest implements ApplicationServiceRequestInterface
{
    private $request;
    
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
    
    protected function getParameterBag()
    {
        return $this->getRequest()->getMethod() == 'GET' ? $this->getRequest()->query : $this->getRequest()->request;
    }

    public function getDataFromIndex($index)
    {
        $result = $this->getParameterBag()->get($index);
        
        if (is_null($result)) {
            return null;
        }
        
        return $result;
    }
    
    public function getFiles()
    {
        return $this->getRequest()->files;
    }
}
