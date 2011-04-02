<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService;

use Doctrine\Common\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;
use ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter\ValidationErrorsFormatterInterface;

abstract class ApplicationService implements ApplicationServiceInterface
{
    // Concurrency Locking Constants
    const CONCURRENCY_LOCK_NONE                 = 1;
    const CONCURRENCY_LOCK_PESSIMISTIC_READ     = 2;
    const CONCURRENCY_LOCK_PESSIMISTIC_WRITE    = 3;
    const CONCURRENCY_LOCK_OPTIMISTIC           = 4;
    
    // Finder Special Operators for Fields
    const FILTER_NOT_EQUAL_TO                   = ':!=:';
    const FILTER_LIKE                           = ':%:';
    const FILTER_GREATER_THAN                   = ':>:';
    const FILTER_GREATER_THAN_OR_EQUAL_TO       = ':>=:';
    const FILTER_LESS_THAN                      = ':<:';
    const FILTER_LESS_THAN_OR_EQUAL_TO          = ':<=:';
    const FILTER_EQUAL_TO                       = ':=:';
    const FILTER_AND                            = ':AND:';
    const FILTER_OR                             = ':OR:';
    const FILTER_IN                             = ':IN:';
    const FILTER_INSTANCE_OF                    = ':INSTANCE_OF:';
    
    // Permissions
    const PERMISSIONS_CREATE                    = 'CREATE';
    const PERMISSIONS_EDIT                      = 'EDIT';
    const PERMISSIONS_DELETE                    = 'DELETE';
    const PERMISSIONS_VIEW                      = 'VIEW';
    
    protected $id                           = null;
    protected $request                      = null;
    protected $response                     = null;
    protected $persistenceManager           = null;
    protected $validator                    = null;
    protected $dispatcher                   = null;
    protected $session                      = null;
    protected $concurrencyLockType          = self::CONCURRENCY_LOCK_NONE;
    protected $repository                   = null;
    protected $services                     = array();
    protected $isFlushAutomatic             = true;
    protected $isSubService                 = false;
    protected $container                    = null;
    protected $aclManager                   = null;
    protected $finderOperators              = array();
    protected $permissions                  = array();
    protected $validationErrorsFormatter    = null;
    
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->setServiceRequest($container->get('application_service_abstract.request'));
        $this->setServiceResponse($container->get('application_service_abstract.response'));
        $this->setPersistenceManager($container->get('application_service_abstract.persistence_manager'));
        $this->setValidator($container->get('validator'));
        $this->setDispatcher($container->get('application_service_abstract.event_dispatcher'));
        $this->setSession($container->get('session'));
        $this->setRepository($this->getPersistenceManager()->getRepository($this->getFullEntityClass()));
        $this->setAclManager($container->get('application_service_abstract.acl_manager'));
        $this->setFinderOperators();
        $this->setPermissions();
        $this->setValidationErrorsFormatter($container->get('application_service_abstract.validation_errors_formatter'));
    }
    
    public function setFinderOperators()
    {
        $this->finderOperators = array(
            self::FILTER_NOT_EQUAL_TO               => '!=',
            self::FILTER_LIKE                       => 'LIKE',
            self::FILTER_GREATER_THAN               => '>',
            self::FILTER_GREATER_THAN_OR_EQUAL_TO   => '>=',
            self::FILTER_LESS_THAN                  => '<',
            self::FILTER_LESS_THAN_OR_EQUAL_TO      => '<=',
            self::FILTER_EQUAL_TO                   => '=',
            self::FILTER_AND                        => 'AND',
            self::FILTER_OR                         => 'OR',
            self::FILTER_IN                         => 'IN',
            self::FILTER_INSTANCE_OF                => 'INSTANCE OF'
        );
    }
    
    public function getFinderOperators()
    {
        return $this->finderOperators;
    }
    
    public function setPermissions()
    {
        $this->permissions = array(
            self::PERMISSIONS_CREATE,
            self::PERMISSIONS_EDIT,
            self::PERMISSIONS_DELETE,
            self::PERMISSIONS_VIEW
        );
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
        $this->isFlushAutomatic = $boolean;
    }
    
    public function isFlushAutomatic()
    {
        return $this->isFlushAutomatic;
    }
    
    public function setIsSubService($boolean)
    {
        $this->isSubService = $boolean;
    }
    
    public function getIsSubService()
    {
        return $this->isSubService;
    }
    
    public function isSubService()
    {
        return $this->getIsSubService();
    }
    
    public function isValidService($service)
    {
        return ( $service instanceof ApplicationServiceInterface );
    }
    
    public function addService(ApplicationServiceInterface $service)
    {
        if ($service->getID() === $this->getID()) {
            throw new \LogicException(sprintf('El servicio "%s" posee el mismo ID que el servicio al cual se lo quiere agregar.', $service->getID()));
        } elseif ($this->hasService($service->getID())) {
            throw new \LogicException(sprintf('El servicio con ID "%s" ya ha sido agregado previamente a este servicio.', $service->getID()));
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
            throw new \LogicException(sprintf('No se le ha agregado ningun servicio con ID "%s" a este servicio.', $serviceID));
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
                
                throw new \InvalidArgumentException(sprintf('Uno de los servicios ingresados es invalido. Debe ser una instancia de "%s". Se recibio: "%s".', 'ApplicationServiceInterface', $received));
            }
            
            $this->addService($service);
        }
    }

    public function formatErrorsFromList($object, ConstraintViolationList $errorList, $formatForFieldName = null)
    {
        return $this->getValidationErrorsFormatter()->format($object, $errorList, $formatForFieldName);
    }
    
    /**
     * Validates the object based on its metadata
     *
     * @param mixed The object
     *
     * @return mixed The object
     */
    public function validateObject($object)
    {
        $validator  = $this->getValidator();
        $result     = $validator->validate($object);
        
        if ($result->count() > 0) {
            throw new Exception\ApplicationInvalidDataException($result, $object);
        }
        
        return $object;
    }
    
    /**
     * Convenient method that binds the data to the 
     * object and then runs the validation
     *
     * @param array The data
     * @param mixed The object
     *
     * @return mixed The object with data binded and validated
     */
    public function bindDataToObjectAndValidate(array $data, $object)
    {
        $this->notifyPreDataBindingEvent($data, $object);

        $object = $this->bindDataToObject($data, $object);

        $this->notifyPostDataBindingEvent($data, $object);

        $object = $this->validateObject($object);
        
        return $object;
    }
    
    public function bindDataToObject( array $data, $object )
    {
        $this->validateObjectIsAnInstanceOfEntity( $object );
        
        return $object;
    }

    /**
     * Method to handle exceptions in a generic fashion without having 
     * to include this logic in every service you create. If you want 
     * to customize the array of errors, just override the 
     * formatErrorsFromList method.
     *
     * @param \Exception The exception
     * @param mixed The object
     * @param array The array with the results of the execution of the service
     *
     * @return array The same array with the results, but with a msg, error type and list of errors added
     */
    public function handleException( \Exception $e )
    {
        $response = $this->getServiceResponse();
        
        $response->setIsSuccess( false );
        
        if ( is_a( $e, 'ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface' ) )
        {
            $response->setErrorType( $e->getType() );
            
            switch ( $e->getType() )
            {
                case 'SubServiceException':
                    if ( $this->isSubService() )
                    {
                        throw $e;
                    }
                    else
                    {
                        $this->setServiceResponse( $e->getSubServiceResponse() );
                    }
                    
                    break;
                case 'ApplicationInvalidDataException':
                    $request            = $this->getServiceRequest();
                    $formatFieldName    = !is_null( $request->getDataFromIndex( $this->getRequestDataIndexForEntity() ) ) ? $this->getRequestDataIndexForEntity() : null;
                    
                    $response->setErrorMessage( 'Se produjo un error al intentar procesar su solicitud debido a que algunos de los valores recibidos son invalidos.' );
                    $response->setFieldsErrors( $this->formatErrorsFromList( $e->getEntity(), $e->getErrorList(), $formatFieldName ) );
                    
                    break;
                default:
                    $response->setErrorMessage( $e->getMessage() );
                    
                    break;
            }
        }
        else
        {
            $exception = new Exception\ApplicationUnknownException( $e->getMessage(), 0, $e );
            
            $response->setErrorType( $exception->getType() );
            $response->setErrorMessage( $exception->getMessage() );
        }
        
        // Notificamos el evento
        $this->notifyExceptionEvent( $e );
        
        if ( $this->isSubService() )
        {
            $e = new Exception\SubServiceException( '', 0, null, $this->getServiceResponse() );
            
            throw $e;
        }
    }
    
    // Finder Methods
    public function findFromRequest( $qb = null )
    {
        $request    = $this->getServiceRequest();
        $qb         = $this->getBaseQueryBuilderForFindAction( $qb );
        
        return $this->findBy( $request->getFilters(), $request->getResultsStart(), $request->getResultsLimit(), $request->getSortBy(), $request->getSortType(), $qb );
    }
    
    public function findOneFromRequest( $qb = null )
    {
        $request    = $this->getServiceRequest();
        $qb         = $this->getBaseQueryBuilderForFindOneAction();
        
        return $this->findOneBy( $request->getFilters(), $qb );
    }
    
    public function findOneByPrimaryKey( $id, $lockMode = null , $lockVersion = null, $qb = null )
    {
        $response       = $this->getServiceResponse();
        
        try
        {
            // Notificamos el evento pre_find
            $this->notifyPreFindEvent();
        
            $lockMode   = $this->getEquivalentConcurrencyLockTypeOfPersistenceManager( $lockMode );
            $result     = $this->doFindByPrimaryKey( $id, $lockMode, $lockVersion );
        
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'La operacion se ejecuto correctamente.' );
            $response->setRow( $result );
            
            // Notificamos el evento post_find
            $this->notifyPostFindEvent();
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function findOneBy( array $filters = array(), $qb = null )
    {
        $response = $this->getServiceResponse();
        
        try
        {
            // Notificamos el evento pre_find
            $this->notifyPreFindEvent();
            
            $request        = $this->getServiceRequest();
            $filters        = !empty( $filters ) ? $filters : $request->getFilters();
            $result         = $this->doFind( $filters, null, null, null, null, false, $qb );
            
            if ( empty( $result ) )
            {
                throw new Exception\DatabaseNoResultException();
            }
            else
            {
                $result = $result[ 0 ];
                $result = $this->formatFieldIndexesForResponse( $this->getRequestDataIndexForEntity(), $result );
            }
            
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'La operacion se ejecuto correctamente.' );
            $response->setRow( $result );
            
            // Notificamos el evento post_find
            $this->notifyPostFindEvent();
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function findAll( $start = null, $limit = null, $orderBy = null, $orderType = null, $qb = null )
    {
        return $this->findBy( array(), $start, $limit, $orderBy, $orderType, $qb );
    }
    
    public function findBy( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $qb = null )
    {
        $response = $this->getServiceResponse();
        
        try
        {
            // Notificamos el evento pre_find
            $this->notifyPreFindEvent();
            
            $repository     = $this->getRepository();
            $rows           = $this->doFind( $filters, $start, $limit, $orderBy, $orderType, false, $qb );
            $partialCount   = count( $rows );
            $totalCount     = $this->doFind( $filters, $start, $limit, $orderBy, $orderType, true, $qb );
            
            $response->setIsSuccess( true );
            $response->setSuccessMessage( 'La operacion se ejecuto correctamente.' );
            $response->setRows( $rows );
            $response->setPartialCount( ( int ) $partialCount );
            $response->setTotalCount( ( int ) $totalCount );
            
            // Notificamos el evento post_find
            $this->notifyPostFindEvent();
        }
        catch ( \Exception $e )
        {
            $this->handleException( $e );
        }
        
        return $response;
    }
    
    public function getBaseQueryBuilderForFindAction( $qb = null )
    {
        $qb = is_null( $qb ) ? $this->createQueryBuilderForPersistenceManager() : $qb;
        
        if ( method_exists( $qb, 'getDqlPart' ) )
        {
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

    public function getFinderQueryBuilder( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null )
    {
        if ( is_null( $qb ) )
        {
            $qb = $this->createQueryBuilderForPersistenceManager();
        }
        
        if ( !is_null( $start ) && !is_int( $start ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El parametro "start" debe ser un entero o NULL. Se recibio: "%s".', $start ) );
        }
        
        if ( !is_null( $limit ) && !is_int( $limit ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El parametro "limit" debe ser un entero o NULL. Se recibio: "%s".', $limit ) );
        }
        
        if ( !is_bool( $onlyCount ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El parametro "onlyCount" debe ser un boolean. Se recibio: "%s".', $onlyCount ) );
        }
        
        $orderBy                = !is_null( $orderBy ) ? $orderBy : $this->getDefaultOrderByColumn();
        $orderType              = !is_null( $orderType ) ? $orderType : $this->getDefaultOrderType();
        $validFieldsForOrder    = $this->getValidFieldsForOrder();
        
        if ( !in_array( $orderBy, array_keys( $validFieldsForOrder ) ) )
        {
            throw new Exception\DatabaseInvalidFieldException( sprintf( 'El campo "%s" ingresado para el ordenamiento es invalido.', $orderBy ) );
        }
        
        return $this->getFinderQueryBuilderForPersistenceManager( $filters, $start, $limit, $orderBy, $orderType, $onlyCount, $qb );
    }
    
    public function getFinderQueryBuilderForPersistenceManager( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null )
    {
        $pm = $this->getPersistenceManager();
        
        if ( method_exists( $pm->getPersistenceManager(), 'getDocumentDatabases' ) )
        {
            return $this->getFinderQueryBuilderForDocumentManager( $filters, $start, $limit, $orderBy, $orderType, $onlyCount, $qb );
        }
        else
        {
            return $this->getFinderQueryBuilderForEntityManager( $filters, $start, $limit, $orderBy, $orderType, $onlyCount, $qb );
        }
    }
    
    public function getFinderQueryBuilderForEntityManager( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null )
    {
        $selectDqlPart          = $qb->getDqlPart( 'select' );
        $fromDqlPart            = $qb->getDqlPart( 'from' );
        $actualRootAlias        = $this->getAliasForEntityFromDql( $qb );
        $rootAlias              = $actualRootAlias ? $actualRootAlias : $this->getAliasForDql();
        $validFieldsForSearch   = $this->getValidFieldsForSearch();
        $validFieldsForOrder    = $this->getValidFieldsForOrder();
        
        if ( $onlyCount === true )
        {
            $qb->select( sprintf( 'COUNT( %s )', $rootAlias ) );
        }
        else if ( empty( $selectDqlPart ) )
        {
            $qb->select( $rootAlias );
        }
        
        if ( empty( $fromDqlPart ) )
        {
            $qb->from( $this->getFullEntityClass(), $rootAlias );
        }
        
        foreach ( $filters as $field => $value )
        {
            if ( !is_array( $value ) && trim( $value ) === '' )
            {
                continue;
            }
            
            if ( $field === 'allFields' )
            {
                $orExpr = $qb->expr()->orx();
                
                foreach ( $validFieldsForSearch as $field )
                {
                    // Cambiamos los espacios por %
                    $fieldDql   = strpos( $field, '.' ) === false ? $rootAlias.'.'.$field : $field;
                    $value      = str_replace( ' ', '%', $value );
                    
                    $orExpr->add( $qb->expr()->like( $fieldDql, $qb->expr()->literal( '%'.$value.'%' ) ) );
                }
                
                $qb->andWhere( '( '.$orExpr.' )' );
            }
            else
            {
                $specialOperator    = $this->getSpecialOperatorFromFieldName( $field );
                $testField          = str_replace( $specialOperator, '', $field );
                
                if ( $specialOperator !== false )
                {
                    if ( $specialOperator !== self::FILTER_INSTANCE_OF && $specialOperator !== self::FILTER_AND && $specialOperator !== self::FILTER_OR && $specialOperator !== self::FILTER_IN )
                    {
                        $this->validateFieldForSearch( $testField );
                    }
                    
                    $expr = $this->createExpressionFromFieldName( $qb, $field, $value, $rootAlias );
                    
                    $qb->where( $expr );
                }
                else
                {
                    $this->validateFieldForSearch( $testField );
                    
                    $fieldDql = strpos( $testField, '.' ) === false ? $rootAlias.'.'.$testField : $testField;
                    
                    $qb->andWhere( $qb->expr()->eq( $fieldDql, $value ) );
                }
            }
        }
        
        $orderBy = $validFieldsForOrder[ $orderBy ];
        $orderBy = strpos( $orderBy, '.' ) === false ? $rootAlias.'.'.$orderBy : $orderBy;
        
        $qb->addOrderBy( $orderBy, $orderType );
        
        if ( $onlyCount === false )
        {
            if ( !is_null( $limit ) && $limit > 0 )
            {
                $qb->setMaxResults( $limit );
                
                if ( !is_null( $start ) )
                {
                    $qb->setFirstResult( $start );
                }
            }
        }
        else
        {
            $qb->setFirstResult( null );
            $qb->setMaxResults( null );
        }
        
        return $qb;
    }
    
    public function createExpressionFromFieldName( QueryBuilder $qb, $field, $value, $rootAlias = null )
    {
        $specialOperator    = $this->getSpecialOperatorFromFieldName( $field );
        $field              = str_replace( $specialOperator, '', $field );
        
        if ( !is_null( $rootAlias ) )
        {
            $field = strpos( $field, '.' ) !== false ? $field : $rootAlias.'.'.$field;
        }
        
        if ( $specialOperator === false )
        {
            $specialOperator = self::FILTER_EQUAL_TO;
        }

        switch ( $specialOperator )
        {
            case self::FILTER_GREATER_THAN:
                $expr = $this->getGreaterThanExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_GREATER_THAN_OR_EQUAL_TO:
                $expr = $this->getGreaterThanOrEqualToExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_LESS_THAN:
                $expr = $this->getLessThanExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_LESS_THAN_OR_EQUAL_TO:
                $expr = $this->getLessThanOrEqualToExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_EQUAL_TO:
                $expr = $this->getEqualToExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_NOT_EQUAL_TO:
                $expr = $this->getNotEqualToExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_INSTANCE_OF:
                $expr = $this->getInstanceOfExpression( $qb, $rootAlias, $value );
                
                break;
            case self::FILTER_LIKE:
                $expr = $this->getLikeExpression( $qb, $field, $value );
                
                break;
            case self::FILTER_AND:
                $expr = $this->getAndExpression( $qb, $value );
                
                break;
            case self::FILTER_OR:
                $expr = $this->getOrExpression( $qb, $value );
                
                break;
            case self::FILTER_IN:
                $expr = $this->getInExpression( $qb, $field, $value );
                
                break;
            default:
                throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" ingresado para el campo "%s" es invalido.', $specialOperator, $field ) );
                
                break;
        }
        
        return $expr;
    }

    public function getGreaterThanExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->gt( $field, $value );
    }
    
    public function getGreaterThanOrEqualToExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->gte( $field, $value );
    }
    
    public function getLessThanExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->lt( $field, $value );
    }
    
    public function getLessThanOrEqualToExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->lte( $field, $value );
    }
    
    public function getEqualToExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->eq( $field, $value );
    }
    
    public function getNotEqualToExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->neq( $field, $value );
    }
    
    public function getInstanceOfExpression( QueryBuilder $qb, $alias, $class )
    {
        $validClasses = $this->getValidClassesForInstanceOfOperator();
        
        if ( !in_array( $class, array_keys( $validClasses ) ) )
        {
            throw new Exception\ApplicationGeneralException( sprintf( 'La clase "%s" para el operador "%s" es invalida.', $class, self::FILTER_INSTANCE_OF ) );
        }
        
        $alias  = is_null( $alias ) ? $qb->getRootAlias() : $alias;
        $class  = $validClasses[ $class ];
        
        return $qb->expr()->andx( sprintf( '%s INSTANCE OF %s', $alias, $class ) );
    }
    
    public function getLikeExpression( QueryBuilder $qb, $field, $value )
    {
        return $qb->expr()->like( $field, $qb->expr()->literal( $value ) );
    }
    
    public function getAndExpression( QueryBuilder $qb, $arrayOfFieldsAndValues )
    {
        if ( !is_array( $arrayOfFieldsAndValues ) )
        {
            throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" requiere un array de valores como valor. Se recibió: "%s".', self::FILTER_AND, $arrayOfFieldsAndValues ) );
        }
        
        $expr = $qb->expr()->andx();
        
        foreach ( $arrayOfFieldsAndValues as $index => $value )
        {
            if ( !is_array( $value ) )
            {
                throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" requiere un array de valores como valor. Se recibió: "%s".', self::FILTER_AND, $value ) );
            }
            
            $field = key( $value );
            $value = $value[ $field ];
            
            $expr->add( $this->createExpressionFromFieldName( $qb, $field, $value ) );
        }
        
        return $expr;
    }
    
    public function getOrExpression( QueryBuilder $qb, $arrayOfFieldsAndValues )
    {
        if ( !is_array( $arrayOfFieldsAndValues ) )
        {
            throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" requiere un array de valores como valor. Se recibió: "%s".', self::FILTER_AND, $arrayOfFieldsAndValues ) );
        }
        
        $expr = $qb->expr()->orx();
        
        foreach ( $arrayOfFieldsAndValues as $field => $value )
        {
            if ( !is_array( $value ) )
            {
                throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" requiere un array de valores como valor. Se recibió: "%s".', self::FILTER_AND, $value ) );
            }
            
            $field = key( $value );
            $value = $value[ $field ];
            
            $expr->add( $this->createExpressionFromFieldName( $qb, $field, $value ) );
        }
        
        return $expr;
    }
    
    public function getInExpression( QueryBuilder $qb, $field, $arrayOfValues )
    {
        if ( !is_array( $arrayOfFieldsAndValues ) )
        {
            throw new Exception\ApplicationGeneralException( sprintf( 'El operador "%s" requiere un array de valores como valor. Se recibió: "%s".', self::FILTER_AND, $arrayOfFieldsAndValues ) );
        }
        
        return $qb->expr()->in( $field, $arrayOfValues );
    }
    
    public function validateFieldForSearch( $field )
    {
        $validFieldsForSearch = $this->getValidFieldsForSearch();
        
        if ( !in_array( $field, $validFieldsForSearch ) )
        {
            throw new Exception\DatabaseInvalidFieldException( sprintf( 'El campo "%s" ingresado para la busqueda es invalido.', $field ) );
        }
        
        return true;
    }
    
    public function getFinderQueryBuilderForDocumentManager( array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null )
    {
        $validFieldsForSearch = $this->getValidFieldsForSearch();
        
        foreach ( $filters as $field => $value )
        {
            if ( trim( $value ) === '' )
            {
                continue;
            }
            
            if ( $field === 'allFields' )
            {
                foreach ( $validFieldsForSearch as $field )
                {
                    $qb->field( $field )->equals( '/'.$value.'/' );
                }
            }
            else
            {
                $specialOperator    = $this->getSpecialOperatorFromFieldName( $field );
                $field              = str_replace( $specialOperator, '', $field );
                
                if ( in_array( $field, $validFieldsForSearch ) )
                {
                    $qb->field( $field )->equals( $value );
                }
                else
                {
                    throw new Exception\DatabaseInvalidFieldException( sprintf( 'El campo "%s" ingresado para la busqueda es invalido.', $field ) );
                }
            }
        }
        
        if ( !is_null( $orderBy ) && !is_null( $orderType ) )
        {
            $qb->sort( $orderBy, $orderType );
        }
        
        if ( !is_null( $start ) )
        {
            $qb->skip( $start );
        }
        
        if ( !is_null( $limit ) )
        {
            $qb->limit( $limit );
        }
        
        return $qb;
    }
    
    public function getSpecialOperatorFromFieldName( $fieldName )
    {
        if ( preg_match( '/(:.+:)/', $fieldName, $matches ) )
        {
            return $matches[ 1 ];
        }
        
        return false;
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
        $request        = $this->getServiceRequest();
        $dataIndex      = $this->getRequestDataIndexForEntity();
        $requestData    = $request->getDataFromIndex( $dataIndex );
        $data           = is_null( $requestData ) ? array() : $requestData;
        
        return $this->create( $data );
    }
    
    public function update( array $data, $lockMode = null, $lockVersion = null )
    {
        $response       = $this->getServiceResponse();
        
        try
        {
            $id         = $this->getIDFromData( $data );
            $entity     = $this->getRepository()->find( $id, $lockMode, $lockVersion );
            
            // Notificamos el evento pre_update
            $this->notifyPreUpdateEvent( $data, $entity );
            
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
        $request        = $this->getServiceRequest();
        $dataIndex      = $this->getRequestDataIndexForEntity();
        $requestData    = $request->getDataFromIndex( $dataIndex );
        $data           = is_null( $requestData ) ? array() : $requestData;
        
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
        $this->validateObjectIsAnInstanceOfEntity( $object );
        
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
            
            $object = $this->bindDataToObjectAndValidate( $data, $object );
            
            // Si el Persistence Manager ya contiene a la entidad, entonces es que estamos 
            // modificandola. En caso contrario, estamos creandola
            $action = $pm->contains( $object ) ? 'update' : 'create';
            
            $pm->persist( $object );
            
            if ( $this->isFlushAutomatic() )
            {
                $pm->flush();
            }
            
            // Notificamos el evento "pre_commit"
            $this->notifyPreCommitEvent( $action, $data, $object );
            
            $pm->commitTransaction();
        }
        catch ( \Exception $e )
        {
            $pm->rollbackTransaction();
            $pm->close();
            
            throw $e;
        }
    }
    
    public function doDelete( $id, $lockMode = null, $lockVersion = null )
    {
        $pm = $this->getPersistenceManager();
        
        try
        {
            $pm->beginTransaction();
            
            $object = $this->getRepository()->find( $id, $lockMode, $lockVersion );
            
            $pm->remove( $object );
            
            if ( $this->isFlushAutomatic() )
            {
                $pm->flush();
            }
            
            // Notificamos el evento "pre_commit"
            $this->notifyPreCommitEvent( 'remove', array(), $object );
            
            $pm->commitTransaction();
        }
        catch ( \Exception $e )
        {
            $pm->rollbackTransaction();
            $pm->close();
            
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
        $this->validateObjectIsAnInstanceOfEntity( $entity );
        
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
    
    public function validateObjectIsAnInstanceOfEntity( $object )
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
        $fromDqlPart    = $qb->getDqlPart( 'from' );
        
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
    public function notifyPreFindEvent(array $data)
    {
        $event = new Event\PreFindEvent($this, $data);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_FIND, $event);
    }
    
    public function notifyPostFindEvent(array $data, array $results)
    {
        $event = new Event\PostFindEvent($this, $data, $results);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_FIND, $event);
    }
    
    public function notifyPreCreateEvent(array $data, $entity)
    {
        $event = new Event\PreCreateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_CREATE, $event);
    }
    
    public function notifyPostCreateEvent(array $data, $entity)
    {
        $event = new Event\PostCreateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_CREATE, $event);
    }
    
    public function notifyPreUpdateEvent(array $data, $entity)
    {
        $event = new Event\PreUpdateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_UPDATE, $event);
    }
    
    public function notifyPostUpdateEvent(array $data, $entity)
    {
        $event = new Event\PostUpdateEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_UPDATE, $event);
    }
    
    public function notifyPreDeleteEvent($id, $entity)
    {
        $event = new Event\PreDeleteEvent($this, $id, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_DELETE, $event);
    }
    
    public function notifyPostDeleteEvent( $id, $entity )
    {
        $event = new Event\PostDeleteEvent($this, $id, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_DELETE, $event);
    }
    
    public function notifyPreCommitEvent($action, array $data, $entity)
    {
        $event = new Event\PreCommitEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_COMMIT, $event);
    }

    public function notifyPostCommitEvent($action, array $data, $entity)
    {
        $event = new Event\PostCommitEvent($this, $action, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_COMMIT, $event);
    }

    public function notifyPreDataBindingEvent(array $data, $entity)
    {
        $event = new Event\PreDataBindingEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_PRE_DATA_BINDING, $event);
    }

    public function notifyPostDataBindingEvent(array $data, $entity)
    {
        $event = new Event\PostDataBindingEvent($this, $data, $entity);
        $this->getDispatcher()->dispatch(Event\Event::ON_POST_DATA_BINDING, $event);
    }

    public function notifyExceptionEvent(\Exception $e)
    {
        $event = new Event\ExceptionEvent($this, $e);
        $this->getDispatcher()->dispatch(Event\Event::ON_EXCEPTION, $event);
    }
    
    public function createQueryBuilderForPersistenceManager()
    {
        $qb = null;
        
        if ( $this->isUsingAnODM() )
        {
            $qb = $this->getPersistenceManager()->createQueryBuilder( $this->getFullEntityClass() );
        }
        else
        {
            $qb = $this->getPersistenceManager()->createQueryBuilder();
        }
        
        return $qb;
    }
    
    public function isUsingAnODM()
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
        $entityClass        = $this->getFullEntityClass();
        $reflectionClass    = new \ReflectionClass( $entityClass );
        $properties         = $reflectionClass->getProperties( \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED );
        
        return $properties;
    }
    
    public function getServiceFromEntityName( $moduleName, $entityName )
    {
        $container      = $this->getContainer();
        $moduleManager  = $container->get( 'module_manager' );
        $moduleName     = $moduleName;
        
        if ( $moduleManager->hasModule( $moduleName ) )
        {
            $module         = $moduleManager->getModule( $entityName );
            $serviceName    = $entityName.'_service';
            
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
}
