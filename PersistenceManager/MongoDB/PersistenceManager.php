<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\MongoDB;

use Doctrine\MongoDB\DocumentManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerAbstract;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class PersistenceManager extends PersistenceManagerAbstract
{
    public function createQueryBuilder( $documentName = null )
    {
        return $this->pm->createQueryBuilder( $documentName );
    }
}
