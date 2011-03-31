<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseNoResultException extends \Exception implements ApplicationServiceExceptionInterface
{
	public function getFriendlyMessage()
	{
		return 'No se encontro ningun resultado en la base de datos con la informacion solicitada.';
	}
	
	public function getType()
	{
		return 'DatabaseNoResultException';
	}
}