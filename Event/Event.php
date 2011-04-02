<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Event;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

class Event extends SymfonyEvent
{
    // Event Constants
    const ON_PRE_FIND          = 'onPreFind';
    const ON_POST_FIND         = 'onPostFind';
    const ON_PRE_CREATE        = 'onPreCreate';
    const ON_POST_CREATE       = 'onPostCreate';
    const ON_PRE_UPDATE        = 'onPreUpdate';
    const ON_POST_UPDATE       = 'onPostUpdate';
    const ON_PRE_DELETE        = 'onPreDelete';
    const ON_POST_DELETE       = 'onPostDelete';
    const ON_PRE_COMMIT        = 'onPreCommit';
    const ON_POST_COMMIT       = 'onPostCommit';
    const ON_PRE_DATA_BINDING  = 'onPreDataBinding';
    const ON_POST_DATA_BINDING = 'onPostDataBinding';
    const ON_EXCEPTION         = 'onException';


    protected $service;

    public function __construct(ApplicationServiceInterface $service)
    {
        parent::__construct();

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
