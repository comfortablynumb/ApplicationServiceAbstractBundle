<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Log;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;

class Formatter
{
    const END_LINE = '------------------------------------------------------------';
    
    public function process(ApplicationServiceRequestInterface $request, \Exception $e, $serviceClass = '')
    {
        $msg = sprintf('%s'.PHP_EOL.'  [Service Class] %s'.PHP_EOL.'  [Ip Address] %s'.PHP_EOL.
            '  [URL] %s'.PHP_EOL.'  [HTTP Method] %s'.PHP_EOL.'  [Exception Class] %s'.PHP_EOL.
            '  [Exception Trace] %s'.PHP_EOL.'  [Script Name] %s'.PHP_EOL.'  [Request Raw] %s'.PHP_EOL.self::END_LINE.PHP_EOL,
            $e->getMessage(),
            $serviceClass,
            $request->getClientIp(),
            $request->getMethod(),
            $request->getUri(),
            get_class($e),
            $e->getTraceAsString(),
            $request->getScriptName(),
            $request->__toString());
        
        return $msg;
    }
}