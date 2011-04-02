<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;

class TestApplicationService2 extends ApplicationService
{
	public function getFullEntityClass()
	{
		return 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\Entity\TestEntity';
	}
	
	public function bindDataToObject( array $data, $object )
	{
		return $object;
	}
    
    public function getAliasForDql()
    {
        return 'b';
    }
	
	public function getID()
	{
		return 'test_service2';
	}
	
	public function getDefaultOrderByColumn()
	{
		return 'name';
	}
	
	public function getDefaultOrderType()
	{
		return 'ASC';
	}
}