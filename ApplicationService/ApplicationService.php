<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;
use ENC\Bundle\ApplicationServiceAbstractBundle\Event;
use ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder;
use ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter\ValidationErrorsFormatterInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\Data\DataHolder;
use ENC\Bundle\ApplicationServiceAbstractBundle\Entity\HasCollectionInterface;

abstract class ApplicationService implements ApplicationServiceInterface
{
    // Concurrency Locking Constants
    const CONCURRENCY_LOCK_NONE                 = 1;
    const CONCURRENCY_LOCK_PESSIMISTIC_READ     = 2;
    const CONCURRENCY_LOCK_PESSIMISTIC_WRITE    = 3;
    const CONCURRENCY_LOCK_OPTIMISTIC           = 4;
    
    // Permissions
    const PERMISSIONS_CREATE                    = 'CREATE';
    const PERMISSIONS_EDIT                      = 'EDIT';
    const PERMISSIONS_DELETE                    = 'DELETE';
    const PERMISSIONS_VIEW                      = 'VIEW';
    const PERMISSIONS_MASTER                    = 'MASTER';
    
    protected $id                           = null;
    protected $request                      = null;
    protected $response                     = null;
    protected $persistenceManager           = null;
    protected $validator                    = null;
    protected $dispatcher                   = null;
    protected $session                      = null;
    protected $logger                       = null;
    protected $logFormatter                 = null;
    protected $concurrencyLockType          = self::CONCURRENCY_LOCK_NONE;
    protected $repository                   = null;
    protected $services                     = array();
    protected $isFlushAutomatic             = true;
    protected $isSubService                 = false;
    protected $container                    = null;
    protected $aclManager                   = null;
    protected $permissions                  = array();
    protected $validationErrorsFormatter    = null;
    protected $validEntityPropertiesForAcl  = null;
    protected $entityClassMetadata          = null;
    
    public function __construct(ContainerInterface $container, array $services = array())
    {
        $this->setContainer($container);

        $sapiType = php_sapi_name();
        $request = $container->get('application_service_abstract.request');

        if (substr($sapiType, 0, 3) == 'cli') {
            $request->setRequest(new Request());
        } else {
            $request->setRequest($container->get('request'));
        }

        $this->setServiceRequest($request);
        $this->setServiceResponse($container->get('application_service_abstract.response'));
        $this->setValidator($container->get('validator'));
        $this->setDispatcher($container->get('application_service_abstract.event_dispatcher'));
        $this->setSession($container->get('session'));
        $this->setLogger($container->get('logger'));
        $this->setLogFormatter($container->get('application_service_abstract.log_formatter'));
        $this->setAclManager($container->get('application_service_abstract.acl_manager'));
        $this->setPermissions(array(
            self::PERMISSIONS_CREATE,
            self::PERMISSIONS_EDIT,
            self::PERMISSIONS_DELETE,
            self::PERMISSIONS_VIEW,
            self::PERMISSIONS_MASTER
        ));
        $this->setValidationErrorsFormatter($container->get('application_service_abstract.validation_errors_formatter'));
        $this->setConcurrencyLockType(self::CONCURRENCY_LOCK_NONE);
        
        if (!empty($services)) {
            $this->setServices($services);
        }
    }

    public function getEntityClassMetadata()
    {
        return $this->entityClassMetadata;
    }

    public function setEntityClassMetadata($metadata)
    {
        $this->entityClassMetadata = $metadata;
    }
    
    public function setPermissions(array $permissions = array())
    {
        $this->permissions = $permissions;
    }
    
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getValidationErrorsFormatter()
    {
        return $this->validationErrorsFormatter;
    }

    public function setValidationErrorsFormatter(ValidationErrorsFormatterInterface $formatter)
    {
        $this->validationErrorsFormatter = $formatter;
    }
    
    public function setAclManager($aclManager)
    {
        $this->aclManager = $aclManager;
    }
    
    public function getAclManager()
    {
        return $this->aclManager;
    }
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
    public function setPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
        
        if ($this->getFullEntityClass()) {
            $this->setRepository($persistenceManager->getRepository($this->getFullEntityClass()));
            
            $metadata = $persistenceManager->getClassMetadata($this->getFullEntityClass());
            $this->setEntityClassMetadata($metadata);
        }
    }
    
    public function getPersistenceManager()
    {
        return $this->persistenceManager;
    }
    
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }
    
    public function getValidator()
    {
        return $this->validator;
    }
    
    public function setServiceRequest(ApplicationServiceRequestInterface $request)
    {
        $this->request = $request;
    }
    
    public function getServiceRequest()
    {
        return $this->request;
    }
    
    public function setServiceResponse(ApplicationServiceResponseInterface $response)
    {
        $this->response = $response;
    }
    
    public function getServiceResponse()
    {
        return $this->response;
    }
    
    public function getRepository()
    {
        return $this->repository;
    }
    
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }
    
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
    
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function getSession()
    {
        return $this->session;
    }
    
    public function setSession($session)
    {
        $this->session = $session;
    }
    
    public function getLogger()
    {
        return $this->logger;
    }
    
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function getLogFormatter()
    {
        return $this->logFormatter;
    }
    
    public function setLogFormatter($logFormatter)
    {
        $this->logFormatter = $logFormatter;
    }
    
    public function getSecurityContext()
    {
        return $this->getContainer()->get('security.context');
    }
    
    public function getUser()
    {
        return $this->getSecurityContext()->getToken()->getUser();
    }
    
    public function setAutomaticFlush($boolean)
    {
        $this->isFlushAutomatic = (bool) $boolean;
    }
    
    public function isFlushAutomatic()
    {
        return $this->isFlushAutomatic;
    }
    
    public function setIsSubService($boolean)
    {
        $this->isSubService = (bool) $boolean;
    }
    
    public function isSubService()
    {
        return $this->isSubService;
    }
    
    public function isValidService($service)
    {
        return ($service instanceof ApplicationServiceInterface);
    }
    
    public function addService(ApplicationServiceInterface $service)
    {
        if ($service->getID() === $this->getID()) {
            throw new \LogicException(sprintf('Service "%s" has the same ID than the service which you want to add.', $service->getID()));
        } elseif ($this->hasService($service->getID())) {
            throw new \LogicException(sprintf('Service with ID "%s" has been added previously to this service.', $service->getID()));
        } else {
            $newService = clone $service;
            $newService->setIsSubService(true);
            
            $this->services[$service->getID()] = $newService;
        }
    }
    
    public function getService($serviceID)
    {
        if ( $this->hasService($serviceID)) {
            return $this->services[$serviceID];
        } else {
            throw new \InvalidArgumentException(sprintf('No service with ID "%s" has been added to this service.', $serviceID));
        }
    }
    
    public function getServices()
    {
        return $this->services;
    }
    
    public function hasService($serviceID)
    {
        foreach ($this->services as $s) {
            if ($s->getID() == $serviceID) {
                return true;
            }
        }
        
        return false;
    }
    
    public function setServices(array $services)  
    {
        foreach ($services as $service) {
            if (!$this->isValidService($service)) {
                $received = is_object($service) ? get_class($service) : $service;
                
                throw new \InvalidArgumentException(sprintf('One of the services you tried to add is invalid. It has to be an instance of class "%s". It was received: "%s".', 'ApplicationServiceInterface', $received));
            }
            
            $this->addService($service);
        }
    }

    public function formatErrorsFromList($object, ConstraintViolationList $errorList, $formatForFieldName = null)
    {
        return $this->getValidationErrorsFormatter()->format($object, $errorList, $formatForFieldName);
    }

    public function validateObject($object)
    {
        $validator = $this->getValidator();
        $constraintValidationList = $validator->validate($object);
        
        if ($constraintValidationList->count() > 0) {
            throw new Exception\ApplicationInvalidDataException($constraintValidationList, $object);
        }
        
        return $object;
    }

    public function bindDataToObjectAndValidate(array $data, $object, $isNew)
    {
        // Data Binding
        $this->notifyPreDataBindingEvent($data, $object, $isNew);

        $object = $this->bindDataToObject($data, $object, $isNew);

        $this->notifyPostDataBindingEvent($data, $object, $isNew);

        // Data Validation
        $this->notifyPreDataValidationEvent($object);

        $object = $this->validateObject($object);

        $this->notifyPostDataValidationEvent($object);

        return $object;
    }
    
    public function bindDataToObject(array $data, $object, $isNew)
    {
        $this->validateObjectIsAnInstanceOfEntityClass($object);
        
        return $object;
    }
    
    public function bindEntityToObject(ApplicationServiceInterface $service, $id, $object, $setterMethod)
    {
        if (!method_exists($object, $setterMethod)) {
            throw new \RuntimeException(sprintf('Method "%s::%s" does not exist.', get_class($object), $setterMethod));
        }
        
        $id = $this->normalizeEntityId($id);
        
        if ($id) {
            try {
                $entity = $service->doFindByPrimaryKey($id);
                
                $service->refreshEntity($entity);
                
                $object->$setterMethod($entity);
                
                return true;
            } catch (Exception\DatabaseNoResultException $e) {
                throw new Exception\EntityNotFoundException(sprintf('La entidad con ID "%s" no se ha encontrado. Tal vez alguien la elimino mientras usted ejecutaba la accion.', $id));
            }
        } else {
            return false;
        }
    }
    
    public function bindEntitiesToObject(ApplicationServiceInterface $service, array $entitiesIDs, HasCollectionInterface $object, $objectSetMethod)
    {
        $ids = $this->normalizeEntitiesIds($entitiesIDs);
        
        if (!method_exists($object, $objectSetMethod)) {
            throw new \RuntimeException(sprintf('Method "%s::%s" does not exist.', get_class($object), $objectSetMethod));
        }
        
        try {
            $collection = $object->createCollection();
            
            if (!($collection instanceof Collection)) {
                throw new \RuntimeException(sprintf('Object of class "%s" implementing "%s" must return from method "%s" a "%s" collection',
                    get_class($object),
                    'HasCollectionInterface',
                    'createCollection',
                    'Collection'
                ));
            }
            
            foreach ($ids as $key => $id) {
                $entity = $service->doFindByPrimaryKey($id);
            
                $service->refreshEntity($entity);
                
                $collection->add($entity);
            }
            
            $object->$objectSetMethod($collection);
        } catch (Exception\DatabaseNoResultException $e) {
            $msg = sprintf('La entidad con ID "%s" no fue encontrada. Tal vez alguien la elimino mientras usted ejecutaba la accion.', $id);
            
            throw new Exception\EntityNotFoundException($msg);
        }
    }
    
    public function normalizeEntityId($id)
    {
        if (is_scalar($id) && trim($id) !== '' && preg_match('/[0-9]/', $id) && $id > 0) {
            return (int) $id;
        } else {
            return false;
        }
    }
    
    public function normalizeEntitiesIds(array $ids, $unique = true)
    {
        $normalizedIds = array();
        
        foreach ($ids as $id) {
            $id = $this->normalizeEntityId($id);
            
            if ($id) {
                if ($unique && in_array($id, $normalizedIds)) {
                    continue;
                } else {
                    $normalizedIds[] = $id;
                }
            }
        }
        
        return $normalizedIds;
    }

    public function handleException(\Exception $e)
    {
        $response = $this->getServiceResponse();
        
        $response->setIsSuccess(false);
        
        if ($e instanceof Exception\ApplicationServiceExceptionInterface)
        {
            $response->setErrorType($e->getType());
            
            switch ($e->getType())
            {
                case 'SubServiceException':
                    if ($this->isSubService()) {
                        throw $e;
                    } else {
                        $this->setServiceResponse($e->getSubServiceResponse());
                        
                        if ($e->getPrevious() instanceof Exception\ApplicationServiceExceptionInterface) {
                            $this->getServiceResponse()->setErrorType($e->getPrevious()->getType());
                        } else {
                            $this->getServiceResponse()->setErrorType('ApplicationUnknownException');
                        }
                    }
                    
                    break;
                case 'ApplicationInvalidDataException':
                    $request = $this->getServiceRequest();
                    $formatFieldName = !is_null($request->getDataFromIndex($this->getRequestDataIndexForEntity())) ? $this->getRequestDataIndexForEntity() : null;
                    
                    $response->setErrorMessage($e->getMessage());
                    $response->setFieldsErrors($this->formatErrorsFromList( $e->getEntity(), $e->getErrorList(), $formatFieldName));
                    
                    break;
                default:
                    $response->setErrorMessage($e->getMessage());
                    
                    break;
            }
        } else {
            $exception = new Exception\ApplicationUnknownException($e->getMessage(), 0, $e);
            
            $response->setErrorType($exception->getType());
            $response->setErrorMessage('Se ha producido un error desconocido en la aplicación.');
        }
        
        // Notify Event
        $this->notifyExceptionEvent($e);
        
        // Log Exception
        $this->getLogger()->err($this->getLogFormatter()->process($this, $e));
        
        if ($this->isSubService()) {
            $e = new Exception\SubServiceException('', 0, $e, $this->getServiceResponse());
            
            throw $e;
        }
    }
    
    // Finder Methods
    public function findFromRequest($qb = null)
    {
        $request = $this->getServiceRequest();
        $qb = $this->getBaseQueryBuilderForFindAction($qb);
        
        return $this->findBy($request->getFilters(), $request->getResultsStart(), $request->getResultsLimit(), $request->getSortBy(), $request->getSortType(), $qb);
    }
    
    public function findOneFromRequest($qb = null)
    {
        $request = $this->getServiceRequest();
        $qb = $this->getBaseQueryBuilderForFindOneAction($qb);
        
        return $this->findOneBy($request->getFilters(), $qb);
    }
    
    public function findOneByPrimaryKey($id, $lockMode = null , $lockVersion = null, $qb = null)
    {
        $response = $this->getServiceResponse();
        
        try {
            $data = array('id' => $id);
            
            $dataHolder = new DataHolder($data);
            $this->notifyPreFindEvent($dataHolder, $qb);
            $data = $dataHolder->getData();
            
            $lockMode = !is_null($lockMode) ? $this->getEquivalentConcurrencyLockTypeOfPersistenceManager($lockMode) : $lockMode;
            $result = $this->doFindByPrimaryKey($id, $lockMode, $lockVersion);
        
            $response->setIsSuccess(true);
            $response->setSuccessMessage('La operacion se ejecuto correctamente.');
            $response->setRowObject($result);
            
            // [TODO] FIX THIS. READ BELOW
            $response->setRow(array());
            
            // [TODO] FIX SECOND ARGUMENT. IT MUST BE AN ARRAY WITH THE DATA OF THE OBJECT RETRIEVED
            // BUT THE ACTUAL RESULT IS AN OBJECT
            $this->notifyPostFindEvent($data, array());
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        
        return $response;
    }
    
    public function findOneBy(array $filters = array(), $qb = null)
    {
        $response = $this->getServiceResponse();
        
        try {
            // Notificamos el evento pre_find
            $dataHolder = new DataHolder($filters);
            $this->notifyPreFindEvent($dataHolder, $qb);
            $filters = $dataHolder->getData();
            
            $request = $this->getServiceRequest();
            $filters = !empty( $filters ) ? $filters : $request->getFilters();
            $result = $this->doFind($filters, null, null, null, null, false, $qb);
            
            if (empty($result)) {
                throw new Exception\DatabaseNoResultException();
            } else {
                $result = $result[0];
            }

            // Notificamos el evento post_find ANTES de que se formateen los campos
            $this->notifyPostFindEvent($filters, $result);
            
            // El evento pudo haber cambiado el resultado, asi que lo obtenemos de nuevo
            $tmp = $response->getRow();
            $result = empty($tmp) ? $result : $tmp;
            $result = $this->formatFieldIndexesForResponse($this->getRequestDataIndexForEntity(), $result);

            $response->setIsSuccess(true);
            $response->setSuccessMessage('La operacion se ejecuto correctamente.');
            $response->setRow($result);
            
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        
        return $response;
    }
    
    public function findAll($start = null, $limit = null, $orderBy = null, $orderType = null, $qb = null)
    {
        return $this->findBy(array(), $start, $limit, $orderBy, $orderType, $qb);
    }
    
    public function findBy(array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $qb = null)
    {
        $response = $this->getServiceResponse();
        
        try {
            // Notificamos el evento pre_find
            $dataHolder = new DataHolder($filters);
            $this->notifyPreFindEvent($dataHolder, $qb);
            $data = $dataHolder->getData();
            
            $repository = $this->getRepository();
            $rows = $this->doFind($filters, $start, $limit, $orderBy, $orderType, false, $qb);
            $partialCount = count($rows);
            $totalCount = $this->doFind($filters, $start, $limit, $orderBy, $orderType, true, $qb);
            
            $response->setIsSuccess(true);
            $response->setSuccessMessage('La operacion se ejecuto correctamente.');
            $response->setRows($rows);
            $response->setPartialCount((int) $partialCount);
            $response->setTotalCount((int) $totalCount);
            
            // Notificamos el evento post_find
            $this->notifyPostFindEvent($filters, $rows);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        
        return $response;
    }
    
    public function getBaseQueryBuilderForFindAction( $qb = null )
    {
        $qb = is_null($qb) ? $this->getPersistenceManager()->createQueryBuilder($this->getFullEntityClass()) : $qb;
        
        if ($this->isUsingAnORM()) {
            $alias  = $this->getAliasForDql();
            $select = $this->getBaseSelectString();
            $select = str_replace( '%s', $alias, $select );
            
            $qb->select( $select );
            
            $fromDqlPart = $qb->getDqlPart( 'from' );
            
            if ( empty( $fromDqlPart ) )
            {
                $qb->from( $this->getFullEntityClass(), $alias );
            }
        }
        
        return $qb;
    }
    
    public function getBaseSelectString()
    {
        return '';
    }
    
    public function getBaseQueryBuilderForFindOneAction( $qb = null )
    {
        return $this->getBaseQueryBuilderForFindAction( $qb );
    }

    public function getFinderQueryBuilder(array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null)
    {
        if (!is_null($start) && !is_int($start)) {
            throw new \InvalidArgumentException(sprintf('El parametro "start" debe ser un entero o NULL. Se recibio: "%s".', $start));
        }
        
        if (!is_null($limit) && !is_int($limit)) {
            throw new \InvalidArgumentException(sprintf('El parametro "limit" debe ser un entero o NULL. Se recibio: "%s".', $limit));
        }
        
        if (!is_bool($onlyCount)) {
            throw new \InvalidArgumentException(sprintf('El parametro "onlyCount" debe ser un boolean. Se recibio: "%s".', $onlyCount));
        }
        
        $orderBy = !is_null($orderBy) ? $orderBy : $this->getDefaultOrderByColumn();
        $orderType = !is_null($orderType) ? $orderType : $this->getDefaultOrderType();
        $validFieldsForOrder = $this->getValidFieldsForOrder();
        
        if (!in_array($orderBy, array_keys($validFieldsForOrder))) {
            throw new Exception\DatabaseInvalidFieldException(sprintf('El campo "%s" ingresado para el ordenamiento es invalido.', $orderBy));
        }
        
        return $this->createFinderQueryBuilder()->create($filters, $start, $limit, $orderBy, $orderType, $onlyCount, $qb);
    }

    public function entityExists( $id, $lockMode = null, $lockVersion = null )
    {
        try
        {
            $lockMode   = is_null( $lockMode ) ? null : $this->getEquivalentConcurrencyLockTypeOfPersistenceManager( $lockMode );
            $entity     = $this->findOneByPrimaryKey( $id, $lockMode, $lockVersion );
            
            return true;
        }
        catch ( Exception\DatabaseNoResultException $e )
        {
            return false;
        }
        catch( \Exception $e )
        {
            throw $e;
        }
    }
    
    // Basic CRUD methods
    
    public function create( array $data )
    {
        $response       = $this->getServiceResponse();
        $pm             = $this->getPersistenceManager();
        $connection     = $pm->getConnection();
        
        try
        {
            $class      = $this->getFullEntityClass();
            $entity     = new $class;
            
            // Notificamos el evento pre_create
            $this->notifyPreCreateEvent( $data, $entity );
            
            $this->doSave( $data, $entity );
            
            $data       = array_merge( $data, array( 'id' => $entity->getId() ) );
            
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'El elemento fue creado correctamente.' );
            $response->setRow( $data );
            $response->setRowObject( $entity );
            
            // Notificamos el evento post_create
            $this->notifyPostCreateEvent( $data, $entity );
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function createFromRequest()
    {
        $request = $this->getServiceRequest();
        $dataIndex = $this->getRequestDataIndexForEntity();
        $requestData = $request->getDataFromIndex($dataIndex);
        $data = is_null($requestData) ? array() : $requestData;
        $files = $request->getFiles();
        
        if ($files->has($dataIndex)) {
            $data = array_merge($data, $files->get($dataIndex));
        }
        
        return $this->create($data);
    }
    
    public function update(array $data, $lockMode = null, $lockVersion = null)
    {
        $response       = $this->getServiceResponse();
        
        try
        {
            $id         = $this->getIDFromData( $data );
            $entity     = $this->getRepository()->find( $id, $lockMode, $lockVersion );
            
            // Notificamos el evento pre_update
            $dataHolder = new DataHolder($data);
            $this->notifyPreUpdateEvent($dataHolder, $entity);
            $data = $dataHolder->getData();
            
            $this->doSave( $data, $entity, $lockMode, $lockVersion );
            
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'El elemento fue actualizado correctamente.' );
            $response->setRow( $data );
            $response->setRowObject( $entity );
            
            // Notificamos el evento post_update
            $this->notifyPostUpdateEvent( $data, $entity );
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function updateFromRequest()
    {
        $request = $this->getServiceRequest();
        $dataIndex = $this->getRequestDataIndexForEntity();
        $requestData = $request->getDataFromIndex($dataIndex);
        $data = is_null($requestData) ? array() : $requestData;
        $files = $request->getFiles();
        
        if ($files->has($dataIndex)) {
            $data = array_merge($data, $files->get($dataIndex));
        }
        
        return $this->update( $data );
    }
    
    public function delete( $id, $lockMode = null, $lockVersion = null )
    {
        $response = $this->getServiceResponse();
        
        try
        {
            if ( !preg_match( '/[0-9]+/', $id ) )
            {
                throw new Exception\ApplicationMissingIDException( 'El ID enviado es invalido. Debe ser un numero entero.' );
            }
            
            $entity = $this->getRepository()->find( $id, $lockMode, $lockVersion );
            
            // Notificamos el evento pre_delete
            $this->notifyPreDeleteEvent( $id, $entity );
            
            $this->doDelete( $id, $lockMode, $lockVersion );
            
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'El elemento se ha eliminado correctamente.' );
            
            // Notificamos el evento post_delete
            $this->notifyPostDeleteEvent( $id, $entity );
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function deleteFromRequest()
    {
        $request            = $this->getServiceRequest();
        $ID                 = $request->getPrimaryKey();
        
        return $this->delete( $ID );
    }
    
    public function doFind( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null, $hydrationMode = AbstractQuery::HYDRATE_ARRAY )
    {
        $qb     = $this->getFinderQueryBuilder( $filters, $start, $limit, $orderBy, $orderType, $onlyCount, $qb );
        $query  = $qb->getQuery();
        
        if ( $this->isUsingAnORM() )
        {
            return $onlyCount === true ? $query->getSingleScalarResult() : $query->getResult( $hydrationMode );
        }
        else
        {
            if ( $hydrationMode === AbstractQuery::HYDRATE_ARRAY )
            {
                $query->setHydrate( false );
            }
            
            $results = $query->execute();
            
            if ( $onlyCount === true )
            {
                return count( $results );
            }
            else
            {
                if ( $hydrationMode === AbstractQuery::HYDRATE_ARRAY )
                {
                    $results = $this->getArrayResultsFromMongoDBResults( $results );
                }
                
                return $results;
            }
        }
    }
    
    public function getArrayResultsFromMongoDBResults( $results )
    {
        return $results;
    }
    
    public function doFindByPrimaryKey( $id, $lockMode = null, $lockVersion = null )
    {
        try
        {
            $result = $this->getRepository()->find( $id, $lockMode, $lockVersion );
            
            if ( is_null( $result ) )
            {
                throw new Exception\DatabaseNoResultException();
            }
        }
        catch ( \Doctrine\ORM\OptimisticLockException $e )
        {
            $msg = 'Ocurrio un error de concurrencia en la base de datos. Probablemente otra persona envio sus modificaciones sobre la entidad antes de que usted las envie. Por favor, realice sus cambios nuevamente.';
            
            throw new Exception\DatabaseConcurrencyException( $msg, ( int ) $e->getCode(), $e );
        }
        catch ( \Doctrine\ORM\PessimisticLockException $e )
        {
            $msg = 'Alguien requirio un bloqueo exclusivo para una de las entidades que usted desea modificar.';
            
            throw new Exception\DatabaseConcurrencyException( $msg, ( int ) $e->getCode(), $e );
        }
        catch ( \Exception $e )
        {
            throw $e;
        }
        
        return $result;
    }
    
    public function doSave( array $data, $object, $lockMode = null, $lockVersion = null )
    {
        $this->validateObjectIsAnInstanceOfEntityClass( $object );
        
        if ( !is_null( $lockVersion ) && ( !$this->concurrencyLockIsOptimistic() && $lockMode !== ApplicationService::CONCURRENCY_LOCK_OPTIMISTIC ) )
        {
            throw new \LogicException( sprintf( 'Si ingresa la version de la entidad, el tipo de lock de concurrencia debe ser "%s".', 'ApplicationService::CONCURRENCY_LOCK_OPTIMISTIC' ) );
        }
        
        $pm = $this->getPersistenceManager();
        
        try
        {
            $pm->beginTransaction();
            
            if ( !is_null( $lockMode ) || $this->concurrencyLockIsEnabled() )
            {
                $lockMode = !is_null( $lockMode ) ? $lockMode : $this->getConcurrencyLockType();

                $this->getConcurrencyLock( $object, $lockMode, $lockVersion );
            }
            
            $object = $this->bindDataToObjectAndValidate($data, $object, !$pm->contains($object));
            
            // Notificamos el evento "pre_commit"
            //
            // Si el Persistence Manager ya contiene a la entidad, entonces es que estamos 
            // modificandola. En caso contrario, estamos creandola

            $action = $pm->contains( $object ) ? 'update' : 'create';

            $this->notifyPrePersistEvent( $action, $data, $object );

            $pm->persist( $object );

            $this->notifyPostPersistEvent( $action, $data, $object );
            
            if ( $this->isFlushAutomatic() )
            {
                $pm->flush();
            }

            $this->notifyPreCommitEvent( $action, $data, $object );
            
            $pm->commitTransaction();
            
            $this->notifyPostCommitEvent( $action, $data, $object );
        }
        catch ( \Exception $e )
        {
            if ($pm->isTransactionActive()) {
                $pm->rollbackTransaction();
            }

            if (!$this->isSubService()) {
                $pm->close();
            }
            
            throw $e;
        }
    }
    
    public function doDelete( $id, $lockMode = null, $lockVersion = null )
    {
        $pm = $this->getPersistenceManager();
        
        try {
            $pm->beginTransaction();
            $object = $this->doFindByPrimaryKey($id, $lockMode, $lockVersion);
            
            $pm->remove($object);
            
            if ($this->isFlushAutomatic()) {
                $pm->flush();
            }
            
            // Notificamos el evento "pre_commit"
            $action = 'remove';
            $data = array('id' => $id);
            $this->notifyPreCommitEvent($action, $data, $object);
            
            $pm->commitTransaction();
            
            $this->notifyPostCommitEvent($action, $data, $object);
        } catch (Exception\DatabaseNoResultException $e) {
            throw new Exception\EntityNotFoundException('La entidad que se desea eliminar no se encuentra en la base de datos. Tal vez usted ya no tiene permisos para verla, u otra persona la ha eliminado antes.');
        } catch (\Exception $e) {
            if ($pm->isTransactionActive()) {
                $pm->rollbackTransaction();
            }

            if (!$this->isSubService()) {
                $pm->close();
            }
            
            throw $e;
        }
    }
    
    public function setConcurrencyLockType( $concurrencyLockType )
    {
        $this->checkIfConcurrencyLockTypeIsValid( $concurrencyLockType );
        
        $this->concurrencyLockType = $concurrencyLockType;
    }
    
    public function checkIfConcurrencyLockTypeIsValid( $lockType )
    {
        $reflection     = new \ReflectionObject( $this );
        $validValues    = $reflection->getConstants();
        
        if ( !in_array( $lockType, $validValues ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El tipo de locking de concurrencia recibido es invalido. Se recibio: %s', $lockType ) );
        }
        
        return $lockType;
    }
    
    public function getConcurrencyLockType()
    {
        return $this->concurrencyLockType;
    }
    
    public function concurrencyLockIsOptimistic()
    {
        return $this->getConcurrencyLockType() === self::CONCURRENCY_LOCK_OPTIMISTIC;
    }
    
    public function concurrencyLockIsPessimisticRead()
    {
        return $this->getConcurrencyLockType() === self::CONCURRENCY_LOCK_PESSIMISTIC_READ;
    }
    
    public function concurrencyLockIsPessimisticWrite()
    {
        return $this->getConcurrencyLockType() === self::CONCURRENCY_LOCK_PESSIMISTIC_WRITE;
    }
    
    public function disableConcurrencyLock()
    {
        $this->setConcurrencyLockType( self::CONCURRENCY_LOCK_NONE );
    }
    
    public function enableOptimisticConcurrencyLock()
    {
        $this->setConcurrencyLockType( self::CONCURRENCY_LOCK_OPTIMISTIC );
    }
    
    public function enablePessimisticReadConcurrencyLock()
    {
        $this->setConcurrencyLockType( self::CONCURRENCY_LOCK_PESSIMISTIC_READ );
    }
    
    public function enablePessimisticWriteConcurrencyLock()
    {
        $this->setConcurrencyLockType( self::CONCURRENCY_LOCK_PESSIMISTIC_WRITE );
    }
    
    public function concurrencyLockIsEnabled()
    {
        return ( $this->getConcurrencyLockType() !== self::CONCURRENCY_LOCK_NONE );
    }
    
    public function getEquivalentConcurrencyLockTypeOfPersistenceManager( $concurrencyLockType )
    {
        $lockType = '';
        
        switch ( $concurrencyLockType )
        {
            case self::CONCURRENCY_LOCK_OPTIMISTIC:
                $lockType = \Doctrine\DBAL\LockMode::OPTIMISTIC;
                
                break;
            case self::CONCURRENCY_LOCK_PESSIMISTIC_READ:
                $lockType = \Doctrine\DBAL\LockMode::PESSIMISTIC_READ;
                
                break;
            case self::CONCURRENCY_LOCK_PESSIMISTIC_WRITE:
                $lockType = \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE;
                
                break;
            default:
                throw new \InvalidArgumentException( sprintf( 'El primer argumento debe ser un tipo de locking valido con un equivalente en Doctrine DBAL. Se ingreso "%s"', $concurrencyLockType ) );
                
                break;
        }
        
        return $lockType;
    }
    
    public function getConcurrencyLock( $entity, $lockMode = null, $expectedVersion = null )
    {
        $this->validateObjectIsAnInstanceOfEntityClass( $entity );
        
        $lockMode = !is_null( $lockMode ) ? $this->checkIfConcurrencyLockTypeIsValid( $lockMode ) : $this->getConcurrencyLockType();
        
        if ( $lockMode === ApplicationService::CONCURRENCY_LOCK_NONE )
        {
            throw new \LogicException( 'Debe elegir al menos un modo de locking de concurrencia. No esta activado el modo por defecto y se recibio NULL o NONE como lock mode en el metodo.' );
        }
        else if ( $lockMode === ApplicationService::CONCURRENCY_LOCK_OPTIMISTIC && ( !is_int( $expectedVersion ) || $expectedVersion < 1 ) )
        {
            $received = is_object( $expectedVersion ) ? get_class( $expectedVersion ) : $expectedVersion;
            
            throw new \LogicException( sprintf( 'Si elige el tipo de locking de concurrencia OPTIMISTA debe ingresar la version esperada de la entidad, la cual debe ser un entero mayor a 0. Se recibio: "%s"', $received ) );
        }
        
        $lockMode = $this->getEquivalentConcurrencyLockTypeOfPersistenceManager( $lockMode );
        
        $this->getPersistenceManager()->lock( $entity, $lockMode, $expectedVersion );
    }
    
    public function validateObjectIsAnInstanceOfEntityClass( $object )
    {
        if ( !is_a( $object, $this->getFullEntityClass() ) )
        {
            $received = is_object( $object ) ? get_class( $object ) : $object;
            
            throw new \InvalidArgumentException( sprintf( 'El segundo argumento debe ser una instancia de la entidad asignada a este servicio. Se recibio: "%s".', $received ) );
        }
        
        return true;
    }
    
    public function isDataForUpdateEntity( array $data )
    {
        $primaryKeyField = $this->getPrimaryKeyFieldOfEntity();
        
        return isset( $data[ $primaryKeyField ] );
    }
    
    public function getPrimaryKeyFieldOfEntity()
    {
        $pm                 = $this->getPersistenceManager();
        $metadata           = $pm->getClassMetadata( $this->getFullEntityClass() );
        $primaryKeyField    = $metadata->getSingleIdentifierFieldName();
        
        return $primaryKeyField;
    }
    
    public function getIDFromData( array $data )
    {
        $primaryKeyField = $this->getPrimaryKeyFieldOfEntity();
        
        if ( isset( $data[ $primaryKeyField ] ) )
        {
            return $data[ $primaryKeyField ];
        }
        else
        {
            throw new Exception\ApplicationMissingIDException( 'El ID de la entidad a modificar o eliminar es obligatorio.' );
        }
    }
    
    public function getValidFieldsForSearch()
    {
        $pm                     = $this->getPersistenceManager();
        $metadata               = $pm->getClassMetadata( $this->getFullEntityClass() );
        $fields                 = $metadata->getReflectionProperties();
        
        if ( method_exists( $metadata, 'getAssociationMappings' ) )
        {
            $associationFields      = $metadata->getAssociationMappings();
            
            // [TODO] Por ahora sacamos los campos definidos para la relacion entre entidades
            // Verificar si se puede implementar una busqueda en los campos de la entidad relacionada
            if ( !empty( $associationFields ) )
            {
                $associationFields = array_keys( $associationFields );
                
                foreach ( $fields as $key => $field )
                {
                    if ( in_array( $field->getName(), $associationFields ) )
                    {
                        unset( $fields[ $key ] );
                    }
                }
            }
        }
        
        return array_keys( $fields );
    }
    
    public function getValidFieldsForOrder()
    {
        $pm                     = $this->getPersistenceManager();
        $metadata               = $pm->getClassMetadata( $this->getFullEntityClass() );
        $fields                 = $metadata->getReflectionProperties();
        
        if ( method_exists( $metadata, 'getAssociationMappings' ) )
        {
            $associationFields      = $metadata->getAssociationMappings();
            
            // [TODO] Por ahora sacamos los campos definidos para la relacion entre entidades
            // Verificar si se puede implementar una busqueda en los campos de la entidad relacionada
            if ( !empty( $associationFields ) )
            {
                $associationFields = array_keys( $associationFields );
                
                foreach ( $fields as $key => $field )
                {
                    if ( in_array( $field->getName(), $associationFields ) )
                    {
                        unset( $fields[ $key ] );
                    }
                }
            }
        }
        
        $arrayOfFields = array_keys( $fields );
        
        return array_combine( $arrayOfFields, $arrayOfFields );
    }
    
    public function getRequestDataIndexForEntity()
    {
        return str_replace( '_service', '', $this->getID() );
    }
    
    public function formatFieldIndexesForResponse( $format, $row )
    {
        foreach ( $row as $key => $value )
        {
            $keyField           = $format.'['.str_replace( '.', '][', $key ).']';
            $keyField           = substr( $key, -3 ) === 'IDs' ? $keyField.'[]' : $keyField;
            $row[ $keyField ]   = $value;
            
            unset( $row[ $key ] );
        }
        
        return $row;
    }
    
    public function getAliasForEntityFromDql( QueryBuilder $qb )
    {
        $fromDqlPart = $qb->getDqlPart( 'from' );
        
        foreach ( $fromDqlPart as $fromPart )
        {
            if ( $fromPart->getFrom() === $this->getFullEntityClass() )
            {
                return $fromPart->getAlias();
            }
        }
        
        return false;
    }
    
    // Global event notifiers
    public function notifyPreFindEvent(DataHolder $data, $qb = null)
    {
        $event = new Event\PreFindEvent($this, $data, $qb);
        $this->getDispatcher()->dispatch(Event\Event::PRE_FIND, $event);
    }
    
    public function notifyPostFindEvent(array $data, array $results)
    {
        $event = new Event\PostFindEvent($this, $data, $results);
        $this->getDispatcher()->dispatch(Event\Event::POST_FIND, $event);
    }
    
    public function notifyPreCreateEvent(array $data, $entity)
    {
        $event = new Event\PreCreateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_CREATE, $event);
    }
    
    public function notifyPostCreateEvent(array $data, $entity)
    {
        $event = new Event\PostCreateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_CREATE, $event);
    }
    
    public function notifyPreUpdateEvent(DataHolder $data, $entity)
    {
        $event = new Event\PreUpdateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_UPDATE, $event);
    }
    
    public function notifyPostUpdateEvent(array $data, $entity)
    {
        $event = new Event\PostUpdateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_UPDATE, $event);
    }
    
    public function notifyPreDeleteEvent($id, $entity)
    {
        $event = new Event\PreDeleteEvent($this, $id, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_DELETE, $event);
    }
    
    public function notifyPostDeleteEvent( $id, $entity )
    {
        $event = new Event\PostDeleteEvent($this, $id, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_DELETE, $event);
    }

    public function notifyPrePersistEvent($action, array $data, $entity)
    {
        $event = new Event\PrePersistEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_PERSIST, $event);
    }

    public function notifyPostPersistEvent($action, array $data, $entity)
    {
        $event = new Event\PostPersistEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_PERSIST, $event);
    }

    public function notifyPreCommitEvent($action, array $data, $entity)
    {
        $event = new Event\PreCommitEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_COMMIT, $event);
    }

    public function notifyPostCommitEvent($action, array $data, $entity)
    {
        $event = new Event\PostCommitEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_COMMIT, $event);
    }

    public function notifyPreDataBindingEvent(array $data, $entity)
    {
        $event = new Event\PreDataBindingEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_DATA_BINDING, $event);
    }

    public function notifyPostDataBindingEvent(array $data, $entity)
    {
        $event = new Event\PostDataBindingEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_DATA_BINDING, $event);
    }

    public function notifyPreDataValidationEvent($entity)
    {
        $event = new Event\PreDataValidationEvent($this, $entity);
        $this->getDispatcher()->dispatch(Event\Event::PRE_DATA_VALIDATION, $event);
    }

    public function notifyPostDataValidationEvent($entity)
    {
        $event = new Event\PostDataValidationEvent($this, $entity);
        $this->getDispatcher()->dispatch(Event\Event::POST_DATA_VALIDATION, $event);
    }

    public function notifyExceptionEvent(\Exception $e)
    {
        $event = new Event\ExceptionEvent($this, $e);
        $this->getDispatcher()->dispatch(Event\Event::EXCEPTION, $event);
    }

    public function isUsingAnODM($whichODM = 'mongodb')
    {
        $pm = $this->getPersistenceManager();
        
        return method_exists( $pm->getPersistenceManager(), 'getDocumentDatabases' );
    }
    
    public function isUsingAnORM()
    {
        return !$this->isUsingAnODM();
    }
    
    public function getValidClassesForInstanceOfOperator()
    {
        return array();
    }
    
    public function getValidEntityPropertiesForAcl()
    {
        if ( $this->validEntityPropertiesForAcl === null )
        {
            $entityClass = $this->getFullEntityClass();
            $reflectionClass = new \ReflectionClass($entityClass);
            $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
            $count = count($properties);
            $returnProperties = array();

            for ($i = 0 ; $i < $count ; ++$i ) {
                if ($properties[$i]->getName() !== 'id') {
                    $returnProperties[] = $properties[$i]->getName();
                }
            }

            $this->validEntityPropertiesForAcl = $returnProperties;
        }

        return $this->validEntityPropertiesForAcl;
    }

    public function getAliasesForEntityProperties()
    {
        $properties = $this->getValidEntityPropertiesForAcl();
        $aliases = array();

        foreach ($properties as $property) {
            $aliases[$this->getFullEntityClass().'.'.$property] = array($property);
        }

        return $aliases;
    }
    
    public function getServiceFromEntityName( $moduleName, $entityName )
    {
        $container      = $this->getContainer();
        $moduleManager  = $container->get( 'module_manager' );
        $moduleName     = $moduleName;
        
        if ( $moduleManager->hasModule( $moduleName ) )
        {
            $module = $moduleManager->getModule( $moduleName );
            $entityUnderscored = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $entityName));
            $serviceName = $entityUnderscored.'_service';
            
            if ( $module->hasService( $serviceName ) )
            {
                return $module->getService( $serviceName );
            }
        }
        
        return false;
    }
    
    public function getClassFromEntityName( $moduleName, $entityName )
    {
        $service = $this->getServiceFromEntityName( $moduleName, $entityName );
        
        if ( $service )
        {
            return $service->getFullEntityClass();
        }
        else
        {
            return false;
        }
    }
    
    public function createFinderQueryBuilder()
    {
        if ($this->isUsingAnORM()) {
            return new FinderQueryBuilder\ORM\FinderQueryBuilder(
                $this->getPersistenceManager(), 
                $this->getFullEntityClass(), 
                $this->getAliasForDql(), 
                $this->getValidFieldsForSearch(),
                $this->getValidFieldsForOrder()
            );
        } else {
            return new FinderQueryBuilder\ODM\MongoDB\FinderQueryBuilder(
                $this->getPersistenceManager(), 
                $this->getFullEntityClass(), 
                $this->getAliasForDql(), 
                $this->getValidFieldsForSearch(),
                $this->getValidFieldsForOrder()
            );
        }
    }
    
    public function getBooleanValueFromString($stringValue)
    {
        $stringValue = (string) $stringValue;
        
        switch ($stringValue) {
            case 'yes':
            case '1':
            case 'on':
            case 'true':
                return true;
                
                break;
            case 'no':
            case '0':
            case 'off':
            case 'false':
                return false;
                
                break;
            default:
                return null;
        }
    }
    
    public function refreshEntity($entity)
    {
        $this->getPersistenceManager()->refresh($entity);
    }

    public function getAliasForDql()
    {
        return false;
    }    

    public function getDefaultOrderByColumn()
    {
        return false;
    }

    public function getDefaultOrderType()
    {
        return false;
    }

    public function getFullEntityClass()
    {
        return false;
    }
}
