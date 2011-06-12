<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\PersistenceManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\Tests\PersistenceManager\TestConnection;

class TestPersistenceManager implements PersistenceManagerInterface
{
	protected $connection = null;
	
	public function __construct( $connection = null )
	{
		$this->connection = is_null( $connection ) ? new TestConnection() : $connection;
	}
	
	public function clear( $objectName )
	{
	}

	public function close()
	{
	}

	public function contains( $object )
	{
	}
	
	public function create( $connection, $configuration, $eventManager )
	{
	}

	public function createQueryBuilder( $documentName = null )
	{
	}

	public function detach( $object )
	{
	}

	public function flush( array $options = array() )
	{
	}

	public function getClassMetadata( $className )
	{
	}

	public function getConfiguration()
	{
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public function getEventManager()
	{
	}

	public function getMetadataFactory()
	{
	}

	public function getPartialReference( $entityName, $identifier )
	{
	}

	public function getPersistenceManager()
	{
	}

	public function getProxyFactory()
	{
	}

	public function getReference( $entityName, $identifier )
	{
	}

	public function getRepository( $objectFullClass )
	{
	}

	public function getUnitOfWork()
	{
	}

	public function lock( $object, $lockMode, $lockVersion )
	{
	}

	public function merge( $object )
	{
	}
	
	public function persist( $object )
	{
	}

	public function refresh( $object )
	{
	}

	public function remove( $object )
	{
	}
	
	public function setPersistenceManager( $persistenceManager )
	{
	}
}