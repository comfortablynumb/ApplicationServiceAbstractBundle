<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService;

use Symfony\Component\DependencyInjection\ContainerInterface;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;

class TestApplicationServiceWithRandomID extends ApplicationService
{
    protected $id;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
    
    public function getFullEntityClass()
    {
        return 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Entity\TestEntity';
    }
    
    public function bindDataToObject(array $data, $object)
    {
        return $object;
    }
    
    public function getAliasForDql()
    {
        return 'b';
    }
    
    public function getID()
    {
        if ($this->id === null) {
            $this->id = microtime().rand(0,9999).rand(0,9999);
        }

        return $this->id;
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
