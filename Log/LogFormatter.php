<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Log;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class LogFormatter
{
    const END_LINE = '------------------------------------------------------------';
    const LEFT_PADDING = '    ';
    
    public function process(ApplicationServiceInterface $service, \Exception $e, array $arguments = array())
    {
        $request = $service->getServiceRequest();
        $requestContent = $this->formatRequestContent($request);
        
        $msg = sprintf('%s'.PHP_EOL.self::LEFT_PADDING.'[Service Class] %s'.PHP_EOL.self::LEFT_PADDING.'[Ip Address] %s'.PHP_EOL.
            self::LEFT_PADDING.'[HTTP Method] %s'.PHP_EOL.self::LEFT_PADDING.'[URL] %s'.PHP_EOL.PHP_EOL.self::LEFT_PADDING.'[Exception Class] %s'.PHP_EOL.
            self::LEFT_PADDING.'[Exception Trace] %s'.PHP_EOL.self::LEFT_PADDING.'[Script Name] %s'.PHP_EOL.self::LEFT_PADDING.'[Request Raw] %s'.PHP_EOL.self::END_LINE.PHP_EOL,
            $e->getMessage(),
            get_class($service),
            $request->getClientIp(),
            $request->getMethod(),
            $request->getUri(),
            get_class($e),
            $this->formatExceptionTrace($e),
            $request->getScriptName(),
            $requestContent);
        
        return $msg;
    }
    
    protected function formatRequestContent($request)
    {
        $padding = self::LEFT_PADDING.self::LEFT_PADDING;
        $content = PHP_EOL.$padding.sprintf('%s %s %s', $request->getMethod(), $request->getRequestUri(), $request->getServer()->get('SERVER_PROTOCOL')).PHP_EOL;
        
        foreach ($request->getHeaders()->all() as $key => $value) {
            $content .= $padding.$key.': '.(is_array($value) && !empty($value) ? $value[0] : $value).PHP_EOL;
        }
        
        return $content;
    }
    
    protected function formatExceptionTrace(\Exception $e) 
    {
        $trace = $e->getTrace();
        $result = "\n";
        $padding = self::LEFT_PADDING.self::LEFT_PADDING;
        $counter = 1;
        
        foreach ($trace as $index => $item) {
            $result .= sprintf('%s(%d) File: %s => %s (%s) (Line %s)',
                $padding, 
                $counter++,
                isset($item['file']) ? $item['file'] : '',
                isset($item['function']) ? $item['function'] : '',
                isset($item['args']) ? $this->formatExceptionTraceMethodArguments($item['args']) : '',
                isset($item['line']) ? $item['line'] : '');
            $result .= PHP_EOL;
        }
        
        return $result;
    }
    
    protected function formatExceptionTraceMethodArguments(array $args) 
    {
        $result = '';
        
        foreach ($args as $index => $arg) {
            $tmp = '';
            
            if (is_object($arg)) {
                $tmp = get_class($arg);
            } else if (is_array($arg)) {
                $tmp = 'Array (';
                
                foreach ($arg as $index2 => $arg2) {
                    if (is_object($arg2)) {
                        $tmp .= get_class($arg2);
                    } else if (is_array($arg2)) {
                        $tmp .= 'Array';
                    } else {
                        $tmp .= var_export($arg2, true);
                    }
                    
                    $tmp .= ', ';
                }
                
                $tmp .= ')';
            } else {
                $tmp = var_export($arg, true);
            }
            
            $result .= $tmp.', ';
        }
        
        $result .= '';
        
        return $result;
    }
}