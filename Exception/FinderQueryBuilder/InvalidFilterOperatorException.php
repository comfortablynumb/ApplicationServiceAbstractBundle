<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception\FinderQueryBuilder;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class InvalidFilterOperatorException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getFriendlyMessage()
    {
        return 'Uno de los operadores para los filtros utilizado es invalido.';
    }
    
    public function getType()
    {
        return 'InvalidFilterOperatorException';
    }
}
