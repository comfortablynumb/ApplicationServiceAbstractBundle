<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseTransactionException extends DatabaseException
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error al intentar finalizar la transaccion.';
    }
    
    public function getType()
    {
        return 'DatabaseTransactionException';
    }
}
