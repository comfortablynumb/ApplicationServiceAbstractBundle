<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;
use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\FinderQueryBuilder\FinderQueryBuilderInterface;

/**
 * Abstract PersistanceManager from which ORM and ODM 
 * PersistanceManager classes must inherit from.
 *
 * @author Gustavo Adrian <comfortablynumb@gmail.com
 */
abstract class PersistenceManagerAbstract implements PersistenceManagerInterface
{
    protected $pm = null;
    
    public function getPersistenceManager()
    {
        return $this->pm;
    }

    public function clear($objectName)
    {
        $this->pm->clear($objectName);
    }

    public function close()
    {
        $this->pm->close();
    }

    public function contains($object)
    {
        return $this->pm->contains($object);
    }

    public function create($connection, $configuration, $eventManager)
    {
        return $this->pm->create($connection, $configuration, $eventManager);
    }

    public function detach($object)
    {
        $this->pm->detach($object);
    }

    public function getClassMetadata($className)
    {
        return $this->pm->getClassMetadata($className);
    }

    public function getConfiguration()
    {
        return $this->pm->getConfiguration();
    }

    public function getConnection()
    {
        return $this->pm->getConnection();
    }

    public function getEventManager()
    {
        return $this->pm->getEventManager();
    }

    public function getMetadataFactory()
    {
        return $this->pm->getMetadataFactory();
    }

    public function getPartialReference($entityName, $identifier)
    {
        return $this->pm->getPartialReference($entityName, $identifier);
    }

    public function getProxyFactory()
    {
        return $this->pm->getProxyFactory();
    }

    public function getReference($entityName, $identifier)
    {
        return $this->pm->getReference($entityName, $identifier);
    }

    public function getRepository($objectFullClass)
    {
        return $this->pm->getRepository($objectFullClass);
    }

    public function getUnitOfWork()
    {
        return $this->pm->getUnitOfWork();
    }

    public function merge($object)
    {
        return $this->pm->merge($object);
    }

    public function persist($object)
    {
        $this->pm->persist($object);
    }

    public function refresh($object)
    {
        $this->pm->refresh($object);
    }

    public function remove($object)
    {
        $this->pm->remove($object);
    }
}
