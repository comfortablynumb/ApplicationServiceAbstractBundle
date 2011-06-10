<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

class ApplicationServiceResponseJSONEscapedData extends ApplicationServiceResponseArrayEscapedData
{
    public function getResponse()
    {
        return json_encode(parent::getResponse());
    }
}
