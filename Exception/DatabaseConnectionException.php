<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseConnectionException extends DatabaseException
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error al intentar conectarse a la base de datos del sistema.';
    }
    
    public function getType()
    {
        return 'DatabaseConnectionException';
    }
}
