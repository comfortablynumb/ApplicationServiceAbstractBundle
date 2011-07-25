<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestBaseEntityFactory extends WebTestCase
{
    public function create(array $data = array())
    {
        return $this->createForClass($this->getClassName(), $data);
    }
    
    public function createForClass($class, array $data = array())
    {
        $entity = new $class();
        
        foreach ($data as $field => $value) {
            $method = 'set'.ucfirst($field);
            
            $entity->$method($value);
        }
        
        return $entity;
    }
    
    public function createMock(array $methods = array(), array $constructorParameters = array(), $className = '', $constructorCall = false)
    {
        return $this->createMockForClass($this->getClassName(), $methods, $constructorParameters, $className, $constructorCall);
    }
    
    public function createMockForClass($class, array $methods = array(), array $constructorParameters = array(), $className = '', $constructorCall = false)
    {
        $mock = $this->getMock($class, $methods, $constructorParameters, $className, $constructorCall);
        
        return $mock;
    }
    
    abstract public function getClassName();
}