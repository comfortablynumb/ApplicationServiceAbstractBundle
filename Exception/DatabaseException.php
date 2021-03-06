<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseException extends BaseException
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error en la base de datos.';
    }
    
    public function getType()
    {
        return 'DatabaseException';
    }
}
