<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseConcurrencyException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getFriendlyMessage()
    {
        return 'Ocurrio un error al intentar ejecutar la operacion. Probablemente otro usuario modifico los datos que usted estaba procesando antes de ejecutar la operacion solicitada.';
    }
    
    public function getType()
    {
        return 'DatabaseConcurrencyException';
    }
}
