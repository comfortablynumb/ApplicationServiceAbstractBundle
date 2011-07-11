<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory;

abstract class TestBaseEntityFactory
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
    
    abstract public function getClassName();
}