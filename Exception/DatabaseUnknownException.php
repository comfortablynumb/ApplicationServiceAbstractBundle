<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseUnknownException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error desconocido en la base de datos.';
    }
    
    public function getType()
    {
        return 'DatabaseUnknownException';
    }
}
