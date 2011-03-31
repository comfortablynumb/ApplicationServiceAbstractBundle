<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

/**
 * @author gfalco
 * @version 1.0
 * @created 12-ene-2011 04:17:23 p.m.
 */
class PersistenceManager implements PersistenceManagerInterface
{
	protected $pm = null;
	
	public function __construct( $persistenceManager )
	{
		$this->setPersistenceManager( $persistenceManager );
	}
	
	/**
	 * 
	 * @param objectName
	 */
	public function clear( $objectName )
	{
		$this->pm->clear( $objectName );
	}

	public function close()
	{
		$this->pm->close();
	}

	/**
	 * 
	 * @param object
	 */
	public function contains( $object )
	{
		return $this->pm->contains( $object );
	}

	/**
	 * 
	 * @param connection
	 * @param configuration
	 * @param eventManager
	 */
	public function create( $connection, $configuration, $eventManager )
	{
		return $this->pm->create( $connection, $configuration, $eventManager );
	}

	/**
	 * 
	 * @param documentName
	 */
	public function createQueryBuilder( $documentName = null )
	{
		return $this->pm->createQueryBuilder( $documentName );
	}

	/**
	 * 
	 * @param object
	 */
	public function detach( $object )
	{
		$this->pm->detach( $object );
	}

	/**
	 * 
	 * @param options
	 */
	public function flush( array $options = array() )
	{
		try
		{
			$this->pm->flush( $options );
		}
		catch( \Doctrine\ORM\OptimisticLockException $e )
		{
			$msg = 'Ocurrio un error de concurrencia en la base de datos. Probablemente otra persona envio sus modificaciones sobre la entidad antes de que usted las envie. Por favor, realice sus cambios nuevamente.';
			
			throw new Exception\DatabaseConcurrencyException( $msg, ( int ) $e->getCode(), $e );
		}
		catch( \Doctrine\ORM\PessimisticLockException $e )
		{
			$msg = 'Alguien requirio un bloqueo exclusivo para una de las entidades que usted desea modificar.';
			
			throw new Exception\DatabaseConcurrencyException( $msg, ( int ) $e->getCode(), $e );
		}
		catch( \PDOException $e )
		{
			if ( $e->getCode() === '23000' )
			{
				// Determinamos el codigo del error de la BD
				$code = $this->extractSqlErrorCodeFromMessage( $e->getMessage() );
				
				switch ( $code )
				{
					case '1451':
						$msg = 'No se puede eliminar el elemento porque existe/n otro/s elemento/s haciendo referencia a él. ';
						$msg .= 'Si aún desea proseguir, por favor, primero elimine el/los elemento/s asociado/s antes de intentar eliminar la entidad seleccionada.';
					
						throw new Exception\DatabaseConstraintException( $msg, ( int ) $e->getCode(), $e );
						
						break;
					default:
						throw new Exception\DatabaseUnknownException( 'Se produjo un error desconocido en la Base de Datos.', ( int ) $e->getCode(), $e );
						
						break;
				}
			}
			else
			{
				throw new Exception\DatabaseUnknownException( 'Se produjo un error desconocido en la Base de Datos.', ( int ) $e->getCode(), $e );
			}
		}
		catch( \Exception $e )
		{
			$msg = 'Ocurrio un error desconocido en la aplicacion.';
			
			throw new Exception\ApplicationUnknownException( $msg, ( int ) $e->getCode(), $e );
		}
	}

	/**
	 * 
	 * @param className
	 */
	public function getClassMetadata( $className )
	{
		return $this->pm->getClassMetadata( $className );
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

	/**
	 * 
	 * @param entityName
	 * @param identifier
	 */
	public function getPartialReference( $entityName, $identifier )
	{
		return $this->pm->getPartialReference( $entityName, $identifier );
	}

	public function getPersistenceManager()
	{
		return $this->pm;
	}

	public function getProxyFactory()
	{
		return $this->pm->getProxyFactory();
	}

	/**
	 * 
	 * @param entityName
	 * @param identifier
	 */
	public function getReference( $entityName, $identifier )
	{
		return $this->pm->getReference( $entityName, $identifier );
	}

	/**
	 * 
	 * @param objectName
	 */
	public function getRepository( $objectFullClass )
	{
		return $this->pm->getRepository( $objectFullClass );
	}

	public function getUnitOfWork()
	{
		return $this->pm->getUnitOfWork();
	}

	/**
	 * 
	 * @param object
	 * @param lockMode
	 * @param lockVersion
	 */
	public function lock( $object, $lockMode, $lockVersion )
	{
		try
		{
			$this->pm->lock( $object, $lockMode, $lockVersion );
		}
		catch( \Doctrine\ORM\OptimisticLockException $e )
		{
			$msg = 'Ocurrio un error de concurrencia en la base de datos. Probablemente otra persona envio sus modificaciones sobre la entidad antes de que usted las envie. Por favor, realice sus cambios nuevamente.';
			
			throw new Exception\DatabaseConcurrencyException( $msg, $e->getCode(), $e );
		}
		catch( \Doctrine\ORM\PessimisticLockException $e )
		{
			$msg = 'Alguien requirio un bloqueo exclusivo para una de las entidades que usted desea modificar.';
			
			throw new Exception\DatabaseConcurrencyException( $msg, $e->getCode(), $e );
		}
		catch( \Exception $e )
		{
			$msg = 'Ocurrio un error desconocido en la aplicacion.';
			
			throw new Exception\ApplicationUnknownException( $msg, $e->getCode(), $e );
		}
	}

	/**
	 * 
	 * @param object
	 */
	public function merge( $object )
	{
		return $this->pm->merge( $object );
	}

	/**
	 * 
	 * @param object
	 */
	public function persist( $object )
	{
		$this->pm->persist( $object );
	}

	/**
	 * 
	 * @param object
	 */
	public function refresh( $object )
	{
		$this->pm->refresh( $object );
	}

	/**
	 * 
	 * @param object
	 */
	public function remove( $object )
	{
		$this->pm->remove( $object );
	}
	
	public function setPersistenceManager( $persistenceManager )
	{
		$emClass 	= 'Doctrine\ORM\EntityManager';
		$dmClass 	= 'Doctrine\ODM\MongoDB\DocumentManager';
		
		if ( !is_a( $persistenceManager, $emClass ) && !is_a( $persistenceManager, $dmClass ) )
		{
			$received = is_object( $persistenceManager ) ? get_class( $persistenceManager ) : $persistenceManager;
			
			throw new \InvalidArgumentException( sprintf( 'El primer argumento debe ser una instancia de "%s" o "%s". Se recibio: %s', $emClass, $dmClass, $received ) );
		}
		
		$this->pm = $persistenceManager;
	}
	
	// Utility Methods
	public function extractSqlErrorCodeFromMessage( $message )
	{
		$results = array();
				
		preg_match( '/SQLSTATE\[[0-9]+\]: [a-zA-Z0-9\s]+: ([0-9]{4})/', $message, $results );
		
		return is_array( $results ) && count( $results ) > 1 ? $results[ 1 ] : '';
	}
	
	public function beginTransaction()
	{
		$connection = $this->getConnection();
		
		if ( method_exists( $connection, 'beginTransaction' ) )
		{
			$connection->beginTransaction();
		}
	}
	
	public function commitTransaction()
	{
		$connection = $this->getConnection();
		
		if ( method_exists( $connection, 'commit' ) )
		{
			$connection->commit();
		}
	}
	
	public function rollbackTransaction()
	{
		$connection = $this->getConnection();
		
		if ( method_exists( $connection, 'rollback' ) )
		{
			$connection->rollback();
		}
	}
}
