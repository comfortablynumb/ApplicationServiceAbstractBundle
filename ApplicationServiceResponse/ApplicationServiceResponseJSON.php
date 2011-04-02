<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

class ApplicationServiceResponseJSON extends ApplicationServiceResponseArray
{
    public function getResponse()
    {
        return json_encode($this->data);
    }
}
