<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationException extends BaseException
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error en la aplicación.';
    }
    
    public function getType()
    {
        return 'ApplicationException';
    }
}
