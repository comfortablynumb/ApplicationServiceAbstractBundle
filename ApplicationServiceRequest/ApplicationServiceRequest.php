<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest;

use Symfony\Component\HttpFoundation\Request;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;

abstract class ApplicationServiceRequest implements ApplicationServiceRequestInterface
{
    private $request;
    
    public function __construct( Request $request )
    {
        $this->request = $request;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function setRequest( Request $request )
    {
        $this->request = $request;
    }
    
    protected function getParameterBag()
    {
        return $this->getRequest()->getMethod() == 'GET' ? $this->getRequest()->query : $this->getRequest()->request;
    }

    public function getDataFromIndex( $index )
    {
        $result = $this->getParameterBag()->get( $index );
        
        if ( is_null( $result ) )
        {
            return null;
        }
        
        return $result;
    }
    
    public function getFiles()
    {
        return $this->getRequest()->files;
    }
    
    public function getFilters()
    {
        $filters = $this->getDataFromIndex( 'filters' );
        
        return is_null( $filters ) ? array() : $filters;
    }
    
    public function getParameters()
    {
        return $this->getDataFromIndex( 'parameters' );
    }
    
    public function getPrimaryKey()
    {
        return $this->getDataFromIndex( 'id' );
    }
    
    public function getResultsLimit()
    {
        $limit = $this->getDataFromIndex( 'limit' );
        
        return is_null( $limit ) ? null : ( int ) $limit;
    }
    
    public function getResultsStart()
    {
        $start = $this->getDataFromIndex( 'start' );
        
        return is_null( $start ) ? null : ( int ) $start;
    }
    
    public function getSortBy()
    {
        return $this->getDataFromIndex( 'sort' );
    }
    
    public function getSortType()
    {
        return $this->getDataFromIndex( 'dir' );
    }
}
