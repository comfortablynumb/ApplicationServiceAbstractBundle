<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\ODM\MongoDB;

use Doctrine\ODM\MongoDB\DocumentManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerAbstract;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class PersistenceManager extends PersistenceManagerAbstract
{
    public function __construct(DocumentManager $em)
    {
        $this->setPersistenceManager($em);
    }

    public function setPersistenceManager(DocumentManager $entityManager)
    {
        $this->pm = $entityManager;
    }
    
    public function createQueryBuilder($documentName = null)
    {
        return $this->pm->createQueryBuilder($documentName);
    }

    public function flush(array $options = array())
    {
        $this->pm->flush($options);
    }

    public function lock($object, $lockMode, $lockVersion)
    {

    }

    public function beginTransaction()
    {
    }

    public function commitTransaction()
    {
    }

    public function rollbackTransaction()
    {
    }
}
