<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

abstract class ApplicationServiceResponseJSON extends ApplicationServiceResponse
{
	public function getResponse()
	{
		return json_encode( $this->data );
	}
}