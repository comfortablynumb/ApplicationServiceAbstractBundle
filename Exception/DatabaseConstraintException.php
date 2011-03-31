<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class DatabaseConstraintException extends \Exception implements ApplicationServiceExceptionInterface
{
	public function getFriendlyMessage()
	{
		return 'No se pudo completar la operacion solicitada debido a que una o mas de las reglas definidas en la base de datos, previniendo asi la generacion de inconsistencias en los datos.';
	}
	
	public function getType()
	{
		return 'DatabaseConstraintException';
	}
}