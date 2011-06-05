<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationAccessDeniedException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getFriendlyMessage()
    {
        return 'Usted no posee los permisos requeridos para ejecutar la acción solicitada.';
    }
    
    public function getType()
    {
        return 'ApplicationAccessDeniedException';
    }
}
