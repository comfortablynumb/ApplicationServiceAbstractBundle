<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Log;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

interface LogFormatterInterface
{
    public function process(ApplicationServiceInterface $service, \Exception $e, array $arguments = array());
}