<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;

class Event extends SymfonyEvent
{
    // Event Constants
    const PRE_FIND               = 'application_service.pre_find';
    const POST_FIND              = 'application_service.post_find';
    const PRE_CREATE             = 'application_service.pre_create';
    const POST_CREATE            = 'application_service.post_create';
    const PRE_UPDATE             = 'application_service.pre_update';
    const POST_UPDATE            = 'application_service.post_update';
    const PRE_DELETE             = 'application_service.pre_delete';
    const POST_DELETE            = 'application_service.post_delete';
    const PRE_PERSIST            = 'application_service.pre_persist';
    const POST_PERSIST           = 'application_service.post_persist';
    const PRE_COMMIT             = 'application_service.pre_commit';
    const POST_COMMIT            = 'application_service.post_commit';
    const PRE_DATA_BINDING       = 'application_service.pre_data_binding';
    const POST_DATA_BINDING      = 'application_service.post_data_binding';
    const PRE_DATA_VALIDATION    = 'application_service.pre_data_validation';
    const POST_DATA_VALIDATION   = 'application_service.post_data_validation';
    const EXCEPTION              = 'application_service.exception';


    protected $service;

    public function __construct(ApplicationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setService(ApplicationServiceInterface $service)
    {
        $this->service = $service;
    }
}
