<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestBaseEntityFactory extends WebTestCase
{
    public function create(array $data = array())
    {
        $class = $this->getClassName();
        
        $entity = new $class();
        
        foreach ($data as $field => $value) {
            $method = 'set'.ucfirst($field);
            
            $entity->$method($value);
        }
        
        return $entity;
    }
    
    public function createMock(array $methods = array(), array $constructorParameters = array(), $className = '', $constructorCall = false)
    {
        $mock = $this->getMock($this->getClassName(), $methods, $constructorParameters, $className, $constructorCall);
        
        return $mock;
    }
    
    abstract public function getClassName();
}