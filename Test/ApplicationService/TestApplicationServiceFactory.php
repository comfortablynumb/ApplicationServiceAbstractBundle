<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Validator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use ENC\Bundle\ApplicationServiceAbstractBundle\AclManager\AclManager;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;
use ENC\Bundle\ApplicationServiceAbstractBundle\Test\PersistenceManager\TestPersistenceManager;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestExtJS;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseArray;

class TestApplicationServiceFactory extends WebTestCase
{
    protected static $container = null;
    protected $testServiceClass1 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService';
    protected $testServiceClass2 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService2';

    public static function create($serviceClass = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService')
    {
        $container = self::getNewContainer();
        $service = new $serviceClass($container);
         
        return $service;
    }
    
    public static function createMock($methods = array(), $constructorArguments = array(), $callConstructor = false, $className = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService')
    {
        $instance = new self();
        $service = $instance->getMock($className, $methods, $constructorArguments, '', $callConstructor);
        $pmMock = $instance->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManager', array(), array(), '', false);
        $validator = self::getValidatorMock();
        $dispatcher = self::getTestDispatcher();
        $serviceRequest = new ApplicationServiceRequestExtJS(self::getTestRequest());
        $serviceResponse = new ApplicationServiceResponseArray();

        $service->setPersistenceManager($pmMock);
        $service->setValidator($validator);
        $service->setDispatcher($dispatcher);
        $service->setServiceRequest($serviceRequest);
        $service->setServiceResponse($serviceResponse);
        
        return $service;
    }
    
    public static function getTestRequest()
    {
        return Request::create('uri', 'GET', array());
    }

    public static function getSessionMock()
    {
        $instance = new self();
        
        return $instance->getMock('Symfony\Component\HttpFoundation\Session', array(), array(), '', false);
    }
    
    public static function getValidatorMock()
    {
        $instance = new self();
        
        return $instance->getMock('Symfony\Component\Validator\Validator', array(), array(), '', false);
    }

    public static function getAclProviderMock()
    {
        $instance = new self();
        
        return $instance->getMock('Symfony\Component\Security\Acl\Dbal\AclProvider', array(), array(), '', false);
    }

    public static function getAclManagerMock()
    {
        $instance = new self();
        
        return $instance->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\AclManager\AclManager', array(), array(), '', false);
    }

    public static function getValidationErrorsFormatterMock()
    {
        $instance = new self();
        
        return $instance->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter\ValidationErrorsFormatterInterface', array(), array(), '', false);
    }
    
    public static function getConnectionMock()
    {
        $instance = new self();
        
        return $instance->getMock('Doctrine\DBAL\Connection', array(), array(), '', false);
    }
    
    public static function getQueryBuilderMock()
    {
        $instance = new self();
        
        return $instance->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
    }
    
    public static function getQueryMock()
    {
        $instance = new self();
        
        return $instance->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestQuery', array(), array(), '', false);
    }
    
    public static function getDispatcher()
    {
        return new EventDispatcher();
    }

    public static function getConstraintViolationListMock()
    {
        $instance = new self();
        
        return $instance->getMock('Symfony\Component\Validator\ConstraintViolationList', array(), array(), '', false);
    }
    
    public static function getNewContainer(array $services = array())
    {
        if (self::$container === null) {
            $instance = new self();
        
            if (isset($services['pm'])) {
                $pm = $services['pm'];
            } else {
                $pm = new TestPersistenceManager();
            }
        
            if (isset($services['validator'])) {
                $validator = $services['validator'];
            } else {
                $validator = $instance->getValidatorMock();
            }
        
            if (isset($services['request'])) {
                $serviceRequest = $services['request'];
            } else {
                $request = $instance->getTestRequest();
                $serviceRequest = new ApplicationServiceRequestExtJS($request);
            }
        
            if (isset($services['response'])) {
                $serviceResponse = $services['response'];
            } else {
                $serviceResponse = new ApplicationServiceResponseArray(array('someValue'));
            }
        
            if (isset($services['event_dispatcher'])) {
                $dispatcher = $services['event_dispatcher'];
            } else {
                $dispatcher = $instance->getDispatcher();
            }
        
            $container = new Container();
            $container->set('application_service_abstract.persistence_manager', $pm);
            $container->set('application_service_abstract.request', $serviceRequest);
            $container->set('application_service_abstract.response', $serviceResponse);
            $container->set('application_service_abstract.event_dispatcher', $dispatcher);
            $container->set('validator', $validator);
            $container->set('session', $instance->getSessionMock());
            $container->set('application_service_abstract.acl_manager', $instance->getAclManagerMock());
            $container->set('application_service_abstract.validation_errors_formatter', $instance->getValidationErrorsFormatterMock());

            self::$container = $container;
        }
            
        return self::$container;
    }

    // Services and Kernel methods
    protected function getService($name, $kernel = null)
    {
        return $this->getBootedKernel()->getContainer()->get($name);
    }

    protected function hasService($name, $kernel = null)
    {

        return $this->getBootedKernel()->getContainer()->has($name);
    }
    
    public function getEm()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }
    
    protected function getBootedKernel()
    {
        $this->kernel = $this->createKernel();

        $this->kernel->boot();

        return $this->kernel;
    }
}
