<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationUnknownException extends ApplicationException
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error desconocido en la aplicacion.';
    }
    
    public function getType()
    {
        return 'ApplicationUnknownException';
    }
}
