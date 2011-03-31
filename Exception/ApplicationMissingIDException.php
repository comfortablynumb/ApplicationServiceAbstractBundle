<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationMissingIDException extends \Exception implements ApplicationServiceExceptionInterface
{
	public function getFriendlyMessage()
	{
		return 'El ID de la entidad es obligatorio para ejecutar la operacion.';
	}
	
	public function getType()
	{
		return 'ApplicationMissingIDException';
	}
}