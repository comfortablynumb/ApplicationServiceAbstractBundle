<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationGeneralException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getType()
    {
        return 'ApplicationGeneralException';
    }
}
