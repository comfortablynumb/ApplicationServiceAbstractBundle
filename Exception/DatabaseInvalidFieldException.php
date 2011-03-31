<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseInvalidFieldException extends \Exception implements ApplicationServiceExceptionInterface
{
	public function getFriendlyMessage()
	{
		return 'El campo ingresado es invalido en la base de datos.';
	}
	
	public function getType()
	{
		return 'DatabaseInvalidFieldException';
	}
}