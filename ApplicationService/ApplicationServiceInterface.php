<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationServiceInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerInterface;

interface ApplicationServiceInterface
{
	public function addService( ApplicationServiceInterface $service );
	public function bindDataToObject( array $data, $object );
	public function bindDataToObjectAndValidate( array $data, $object );
	public function concurrencyLockIsEnabled();
	public function concurrencyLockIsOptimistic();
	public function concurrencyLockIsPessimisticRead();
	public function concurrencyLockIsPessimisticWrite();
	public function disableConcurrencyLock();
	public function doFind( array $filters = array(), $orderBy = null, $orderType = null, $start = null, $limit = null, $onlyCount = false, $qb = null, $hydrationMode = AbstractQuery::HYDRATE_ARRAY );
	public function doSave( array $data, $object );
	public function enableOptimisticConcurrencyLock();
	public function enablePessimisticReadConcurrencyLock();
	public function enablePessimisticWriteConcurrencyLock();
	public function formatErrorsFromList( $object, ConstraintViolationList $errorList );
	public function getAliasForDql();
	public function getConcurrencyLock( $entity, $expectedVersion );
	public function getConcurrencyLockType();
	public function getDefaultOrderByColumn();
	public function getDefaultOrderType();
	public function getEquivalentConcurrencyLockTypeOfPersistenceManager( $lockMode );
	public function getFinderQueryBuilder( array $filters = array(), $orderBy = null, $orderType = null, $start = null, $limit = null, $onlyCount = false, $qb = null );
	public function getFullEntityClass();
	public function getID();
	public function getPersistenceManager();
	public function getRepository();
	public function getRequestDataIndexForEntity();
	public function getService( $serviceID );
	public function getServiceRequest();
	public function getServiceResponse();
	public function getServices();
	public function getValidator();
	public function handleException( \Exception $e );
	public function hasService( $serviceID );
	public function setConcurrencyLockType( $concurrencyCheckType );
	public function setPersistenceManager( PersistenceManagerInterface $em );
	public function setRepository( $repository );
	public function setServiceRequest( ApplicationServiceRequestInterface $request );
	public function setServiceResponse( ApplicationServiceResponseInterface $request );
	public function setServices( array $services );
	public function setValidator( Validator $validator );
	public function validateObject( $object );
}
