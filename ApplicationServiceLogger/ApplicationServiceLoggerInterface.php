<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceLogger;

interface ApplicationServiceLogger
{
    public function getID();
    public function log( $messageType, $message, $errorType = null, $serviceID = null, $userID = null, $datetime = null );
}
