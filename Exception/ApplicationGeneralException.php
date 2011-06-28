<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationGeneralException extends ApplicationException
{
    public function getType()
    {
        return 'ApplicationGeneralException';
    }
}
