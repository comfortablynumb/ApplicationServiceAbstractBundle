<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Validator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use ENC\Bundle\ApplicationServiceAbstractBundle\AclManager\AclManager;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;
use ENC\Bundle\ApplicationServiceAbstractBundle\Tests\PersistenceManager\TestPersistenceManager;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceRequest\ApplicationServiceRequestBase;
use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse\ApplicationServiceResponseArray;

class TestApplicationServiceFactory extends WebTestCase
{
    protected $container = null;
    protected $testServiceClass1 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestApplicationService';
    protected $testServiceClass2 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestApplicationService2';
    
    public function getClassName()
    {
        return 'ENC\Bundle\ApplicationServiceAbstractBundle\Service\ApplicationService';
    }
    
    public function create($serviceClass = null, array $services = array())
    {
        if (is_null($serviceClass)) {
            $serviceClass = $this->testServiceClass1;
        }
        
        $container = $this->getNewContainer($services);
        $service = new $serviceClass($container);
         
        return $service;
    }
    
    public function createMock(array $methods = array(), array $constructorParameters = array(), $className = '', $constructorCall = false)
    {
        $mock = $this->getMock($this->getClassName(), $methods, $constructorParameters, $className, $constructorCall);
        
        return $mock;
    }
    
    public function getRequest()
    {
        return Request::create('uri', 'GET', array());
    }

    public function getSessionMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Session', array(), array(), '', false);
    }
    
    public function getValidatorMock()
    {
        return $this->getMock('Symfony\Component\Validator\Validator', array(), array(), '', false);
    }

    public function getAclProviderMock()
    {
        return $this->getMock('Symfony\Component\Security\Acl\Dbal\AclProvider', array(), array(), '', false);
    }

    public function getAclManagerMock()
    {
        return $this->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\AclManager\AclManager', array(), array(), '', false);
    }

    public function getValidationErrorsFormatterMock()
    {
        return $this->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter\ValidationErrorsFormatterInterface', array(), array(), '', false);
    }
    
    public function getConnectionMock()
    {
        return $this->getMock('Doctrine\DBAL\Connection', array(), array(), '', false);
    }
    
    public function getQueryBuilderMock()
    {
        return $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
    }
    
    public function getQueryMock()
    {
        return $this->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestQuery', array(), array(), '', false);
    }

    public function getDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher', array(), array(), '', false);
    }
    
    public function getDispatcher()
    {
        return new EventDispatcher();
    }

    public function getConstraintViolationListMock()
    {
        return $this->getMock('Symfony\Component\Validator\ConstraintViolationList', array(), array(), '', false);
    }
    
    public function getLoggerMock()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '', false);
    }
    
    public function getLogFormatterMock()
    {
        return $this->getMock('ENC\Bundle\ApplicationServiceAbstractBundle\Log\LogFormatterInterface', array(), array(), '', false);
    }

    public function getResponse()
    {
        return new ApplicationServiceResponseArray(array('someField' => 'someValue'));
    }
    
    public function getNewContainer(array $services = array())
    {
        if ($this->$container === null) {
            if (isset($services['pm'])) {
                $pm = $services['pm'];
            } else {
                $pm = new TestPersistenceManager();
            }
        
            if (isset($services['validator'])) {
                $validator = $services['validator'];
            } else {
                $validator = $this->getValidatorMock();
            }
        
            if (isset($services['request'])) {
                $serviceRequest = $services['request'];
            } else {
                $request = $this->getRequest();
                $serviceRequest = new ApplicationServiceRequestBase($request);
            }
        
            if (isset($services['response'])) {
                $serviceResponse = $services['response'];
            } else {
                $serviceResponse = new ApplicationServiceResponseArray(array('someValue'));
            }
        
            if (isset($services['event_dispatcher'])) {
                $dispatcher = $services['event_dispatcher'];
            } else {
                $dispatcher = $this->getDispatcher();
            }
            
            if (isset($services['logger'])) {
                $logger = $services['logger'];
            } else {
                $logger = $this->getLoggerMock();
            }
        
            $container = new Container();
            $container->set('application_service_abstract.persistence_manager.orm', $pm);
            $container->set('application_service_abstract.persistence_manager.odm.mongodb', $pm);
            $container->set('application_service_abstract.request', $serviceRequest);
            $container->set('application_service_abstract.response', $serviceResponse);
            $container->set('application_service_abstract.event_dispatcher', $dispatcher);
            $container->set('validator', $validator);
            $container->set('session', $this->getSessionMock());
            $container->set('logger', $logger);
            $container->set('application_service_abstract.acl_manager', $this->getAclManagerMock());
            $container->set('application_service_abstract.validation_errors_formatter', $this->getValidationErrorsFormatterMock());
            $container->set('application_service_abstract.log_formatter', $this->getLogFormatterMock());

            $this->container = $container;
        }
            
        return $this->container;
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
