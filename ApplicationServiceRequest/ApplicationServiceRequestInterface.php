<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest;

use Symfony\Component\HttpFoundation\Request;

interface ApplicationServiceRequestInterface
{
    public function __construct( Request $request );
    public function getDataFromIndex( $index );
    public function getFiles();
    public function getFilters();
    public function getParameters();
    public function getPrimaryKey();
    public function getRequest();
    public function getResultsLimit();
    public function getResultsStart();
    public function getSortBy();
    public function getSortType();
    public function setRequest( Request $request );
}
