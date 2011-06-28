<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class BaseException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getFriendlyMessage()
    {
        return '';
    }
    
    public function getType()
    {
        return 'BaseException';
    }
}