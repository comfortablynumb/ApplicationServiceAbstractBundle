<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequest;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\Exception;

class ApplicationServiceRequestExtJS extends ApplicationServiceRequest
{
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
