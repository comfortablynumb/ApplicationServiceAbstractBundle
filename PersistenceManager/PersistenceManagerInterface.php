<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager;

/**
 * @author gfalco
 * @version 1.0
 * @created 12-ene-2011 04:07:05 p.m.
 */
interface PersistenceManagerInterface
{
	/**
	 * 
	 * @param objectName
	 */
	function clear( $objectName );
	function close();

	/**
	 * 
	 * @param object
	 */
	function contains( $object );

	/**
	 * 
	 * @param connection
	 * @param configuration
	 * @param eventManager
	 */
	function create( $connection, $configuration, $eventManager );

	/**
	 * 
	 * @param documentName
	 */
	function createQueryBuilder( $documentName = null );

	/**
	 * 
	 * @param object
	 */
	function detach( $object );

	/**
	 * 
	 * @param options
	 */
	function flush( array $options = array() );

	/**
	 * 
	 * @param className
	 */
	function getClassMetadata( $className );

	function getConfiguration();

	function getConnection();

	function getEventManager();

	function getMetadataFactory();

	/**
	 * 
	 * @param entityName
	 * @param identifier
	 */
	function getPartialReference( $entityName, $identifier );

	function getPersistenceManager();

	function getProxyFactory();

	/**
	 * 
	 * @param entityName
	 * @param identifier
	 */
	function getReference( $entityName, $identifier );

	/**
	 * 
	 * @param objectName
	 */
	function getRepository( $objectName );

	function getUnitOfWork();

	/**
	 * 
	 * @param object
	 * @param lockMode
	 * @param lockVersion
	 */
	function lock( $object, $lockMode, $lockVersion );

	/**
	 * 
	 * @param object
	 */
	function merge( $object );

	/**
	 * 
	 * @param object
	 */
	function persist( $object );

	/**
	 * 
	 * @param object
	 */
	function refresh( $object );

	/**
	 * 
	 * @param object
	 */
	function remove( $object );
}