<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;

class TestApplicationService extends ApplicationService
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
        return 'a';
    }
	
	public function getID()
	{
		return 'test_service';
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