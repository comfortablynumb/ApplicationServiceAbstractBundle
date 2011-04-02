<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class ExceptionEvent extends Event
{
    protected $exception;
    
    public function __construct(ApplicationServiceInterface $service, \Exception $exception)
    {
        parent::__construct($service);

        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function setException( \Exception $exception )
    {
        $this->exception = $exception;
    }
}
