<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception\FinderQueryBuilder;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class InvalidDataForFilterOperatorException extends \Exception implements ApplicationServiceExceptionInterface
{
    public function getType()
    {
        return 'InvalidDataForFilterOperatorException';
    }
}
