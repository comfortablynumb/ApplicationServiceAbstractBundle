<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService;

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