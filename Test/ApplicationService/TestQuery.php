<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

class TestQuery
{
	public function getResult( $hydrationMode )
	{
		return array();
	}
	
	public function getSingleScalarResult()
	{
		return 1;
	}
}