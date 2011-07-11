<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;
use ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory\TestApplicationServiceFactory;

class ApplicationServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SERVICE_CLASS1 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestApplicationService';
    const TEST_SERVICE_CLASS2 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestApplicationService2';
    const TEST_SERVICE_CLASS_WITH_RANDOM_ID = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\ApplicationService\TestApplicationServiceWithRandomID';
    
    const TEST_ENTITY = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Entity\TestEntity';
    const TEST_ENTITY_REPOSITORY = 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Entity\TestEntityRepository';

    const EXCEPTION_INVALID_DATA = 'ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationInvalidDataException';
    const EXCEPTION_SUB_SERVICE = 'ENC\Bundle\ApplicationServiceAbstractBundle\Exception\SubServiceException';
    
    protected $factory;
    
    public function setUp()
    {
        $this->factory = new TestApplicationServiceFactory();
    }
    
    public function test_isValidService_returnsTrueIfItsAValidApplicationService()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $this->assertTrue($service->isValidService($service2));
        $this->assertFalse($service->isValidService(new \DateTime()));
    }

    /**
     * @expectedException LogicException
     */
    public function test_addService_doesntLetAddAServiceToItself()
    {
        $service = $this->factory->create();

        $service->addService($service);
    }    

    /**
     * @expectedException LogicException
     */
    public function test_addService_doesntLetAddAServiceToAnotherServiceThatAlreadyHasIt()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);
        $service->addService($service2);
    }

    public function test_addService_whenAddingAServiceToAnotherServiceItsMarkedAsSubservice()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);

        $this->assertTrue($service->hasService($service2->getID()));

        $subService = $service->getService($service2->getID());

        $this->assertTrue($subService->isSubService());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function test_getService_throwsExceptionIfServiceDoesntExist()
    {
        $service = $this->factory->create();

        $service->getService('non_existent_service');
    }
    
    public function test_getService_returnsServiceRequested()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);

        $requestedService = $service->getService($service2->getID());
        
        $this->assertEquals($service2->getID(), $requestedService->getID());
    }
    
    public function test_setService_setsACopyOfTheServiceAndSetsItAsASubService()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);

        $requestedService = $service->getService($service2->getID());
        
        $this->assertNotSame($service2, $requestedService);
        $this->assertNotEquals($service2, $requestedService);
        $this->assertTrue($requestedService->isSubService());
    }

    public function test_getServices_returnsServicesAddedToMainService()
    {
        $service = $this->factory->create();
        $subServicesArray = array(
            $this->factory->create(self::TEST_SERVICE_CLASS2),
            $this->factory->create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID),
            $this->factory->create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID)
        );

        $service->addService($subServicesArray[0]);
        $service->addService($subServicesArray[1]);
        $service->addService($subServicesArray[2]);
        
        $services = $service->getServices();

        $this->assertEquals($services[$subServicesArray[0]->getID()]->getID(), $subServicesArray[0]->getID());
        $this->assertEquals($services[$subServicesArray[1]->getID()]->getID(), $subServicesArray[1]->getID());
        $this->assertEquals($services[$subServicesArray[2]->getID()]->getID(), $subServicesArray[2]->getID());
    }
    
    public function test_hasService_returnsTrueIfAServiceHasAnotherServiceOrFalseOtherwise()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);
        
        $this->assertTrue($service->hasService($service2->getID()));
        $this->assertFalse($service->hasService('someServiceID'));
    }

    public function test_setServices_setServicesCorrectly()
    {
        $service = $this->factory->create();
        $service2 = $this->factory->create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID);

        $subServicesArray = array(
            $this->factory->create(self::TEST_SERVICE_CLASS2),
            $this->factory->create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID),
            $this->factory->create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID)
        );

        $service->setServices($subServicesArray);
        
        $services = $service->getServices();

        $this->assertEquals($services[$subServicesArray[0]->getID()]->getID(), $subServicesArray[0]->getID());
        $this->assertEquals($services[$subServicesArray[1]->getID()]->getID(), $subServicesArray[1]->getID());
        $this->assertEquals($services[$subServicesArray[2]->getID()]->getID(), $subServicesArray[2]->getID());

        $subServicesArray[] = 'InvalidService';

        $this->setExpectedException('InvalidArgumentException');

        $service2->setServices($subServicesArray);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function test_setServices_settingIncorrectServiceThrowsException()
    {
        $service = $this->factory->create();
        
        $subServicesArray[] = 'InvalidService';
        
        $service->setServices($subServicesArray);
    }

    public function test_formatErrorsFromList_callsFormatMethodOfValidationErrorsFormatterInterfaceObject()
    {
        $service = $this->factory->create();
        $formatterMock = $service->getValidationErrorsFormatter();
        $object = new \DateTime();
        $errorsList = $this->factory->getConstraintViolationListMock();
        $formatForFieldName = 'entity';

        $formatterMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($object), $this->equalTo($errorsList), $this->equalTo($formatForFieldName));

        $service->formatErrorsFromList($object, $errorsList, $formatForFieldName);
    }

    public function test_validateObject_callsValidatorsValidateMethodInternally()
    {
        $service = $this->factory->create();
        $validator = $service->getValidator();
        $entityClass = self::TEST_ENTITY; 
        $entity = new $entityClass;
        $constraintViolationList = $this->factory->getConstraintViolationListMock();

        $constraintViolationList->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($constraintViolationList));
        
        $service->validateObject($entity);
    }

    public function test_validateObject_callsValidatorsValidateMethodInternallyAndThrowsApplicationInvalidDataExceptionInCaseOfErrors()
    {
        $service = $this->factory->create();
        $validator = $this->factory->getValidatorMock();
        $entityClass = self::TEST_ENTITY; 
        $entity = new $entityClass;
        $constraintViolationList = $this->factory->getConstraintViolationListMock();

        $constraintViolationList->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($constraintViolationList));
        
        $service->setValidator($validator);
        
        $this->setExpectedException(self::EXCEPTION_INVALID_DATA);

        $service->validateObject($entity);
    }

    public function test_bindDataToObjectAndValidate_callsInternalMethodsToBindValidateAndNotifyEvents()
    {
        $methodsToMock = array(
            'notifyPreDataBindingEvent',
            'bindDataToObject',
            'notifyPostDataBindingEvent',
            'notifyPreDataValidation',
            'validateObject',
            'notifyPostDataValidation'
        );
        $data = array( 'field' => 'value' );
        $object = new \DateTime();

        $serviceMock = $this->factory->createMock($methodsToMock);
        $serviceMock->setDispatcher($this->factory->getDispatcherMock());

        $serviceMock->expects($this->once())
            ->method('notifyPreDataBindingEvent')
            ->with($this->equalTo($data), $this->equalTo($object));
        $serviceMock->expects($this->once())
            ->method('bindDataToObject')
            ->with($this->equalTo($data), $this->equalTo($object))
            ->will($this->returnValue($object));
        $serviceMock->expects($this->once())
            ->method('notifyPostDataBindingEvent')
            ->with($this->equalTo($data), $this->equalTo($object));
        $serviceMock->expects($this->once())
            ->method('notifyPreDataValidationEvent')
            ->with($this->equalTo($object));
        $serviceMock->expects($this->once())
            ->method('validateObject')
            ->with($this->equalTo($object))
            ->will($this->returnValue($object));
        $serviceMock->expects($this->once())
            ->method('notifyPostDataValidationEvent')
            ->with($this->equalTo($object));

        $serviceMock->bindDataToObjectAndValidate($data, $object, true);
    }

    public function test_handleException_ApplicationServiceExceptionInterfaceHandling()
    {
        $service = $this->factory->create();

        $e = new Exception\ApplicationGeneralException('App Error');

        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationGeneralException');
        $this->assertEquals($response->getErrorMessage(), $e->getMessage());
    }

    public function test_handleException_UnknownExceptionInterfaceHandling()
    {
        $service = $this->factory->create();
        $e = new \InvalidArgumentException('App Error');

        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationUnknownException');
    }

    public function test_handleException_SubServiceExceptionWithPreviousNonApplicationServiceExceptionInterface()
    {
        $service = $this->factory->create();
        $response = $this->factory->getResponse();
        
        // If it's a SubServiceException and it has a previous non ApplicationServiceExceptionInterface exception
        // then the response then should have an error type of ApplicationUnknownException
        $subServiceException = new \InvalidArgumentException('Bad Argument');
        $e = new Exception\SubServiceException('App Error', 0, $subServiceException, $response);

        $service->handleException($e);

        $serviceResponse = $service->getServiceResponse();

        $this->assertEquals($serviceResponse, $response);
        $this->assertEquals($serviceResponse->getErrorType(), 'ApplicationUnknownException');
    }
    
    public function test_handleException_SubServiceExceptionWithPreviousApplicationServiceExceptionInterface()
    {
        $service = $this->factory->create();
        $response = $this->factory->getResponse();
        
        // If it's a SubServiceException and it has a previous ApplicationServiceExceptionInterface exception
        // then the response then should have the error type of the previous exception
        $subServiceException = new Exception\ApplicationGeneralException('Exception');
        $e = new Exception\SubServiceException('App Error', 0, $subServiceException, $response);

        $service->handleException($e);
        
        $serviceResponse = $service->getServiceResponse();

        $this->assertEquals($serviceResponse, $response);
        $this->assertEquals($serviceResponse->getErrorType(), 'ApplicationGeneralException');
    }
    
    public function test_handleException_SubServiceExceptionWithPreviousNonApplicationServiceExceptionInterfaceInASubService()
    {
        $service = $this->factory->create();
        $response = $this->factory->getResponse();
        
        // If it's a SubService and it throws a non ApplicationServiceExceptionInterface exception
        // then the service should throw a SubServiceException with original exception as previous exception
        $this->setExpectedException(self::EXCEPTION_SUB_SERVICE);
        
        $service->setIsSubService(true);
        
        $e = new \InvalidArgumentException('Bad Argument');
        $service->handleException($e);
    }
    
    public function test_handleException_SubServiceExceptionInASubServiceShouldReThrowException()
    {
        $service = $this->factory->create();
        $response = $this->factory->getResponse();
        
        // If it's a SubService and it throws a SubServiceException
        // then the service should re-throw the exception
        $this->setExpectedException(self::EXCEPTION_SUB_SERVICE);
        
        $e = new Exception\ApplicationGeneralException('Exception');
        $subException = new Exception\SubServiceException('App Error', 0, $e, $response);
        
        $service->setIsSubService(true);
        
        $service->handleException($subException);
    }

    public function test_handleException_ApplicationInvalidDataExceptionHandling()
    {
        $service = $this->factory->create();
        $entityClass = self::TEST_ENTITY;
        $entity = new $entityClass;
        $constraintViolationList = $this->factory->getConstraintViolationListMock();
        $validationErrorsFormatterMock = $this->factory->getValidationErrorsFormatterMock();
        $formattedErrors = array(
            'field'     => 'error',
            'field2'    => 'error2'
        );
        $validationErrorsFormatterMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($entity), $this->equalTo($constraintViolationList), $this->equalTo(null))
            ->will($this->returnValue($formattedErrors));
        $service->setValidationErrorsFormatter($validationErrorsFormatterMock);
        
        $e = new Exception\ApplicationInvalidDataException($constraintViolationList, $entity, 'Form Errors');
        
        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationInvalidDataException');
        $this->assertEquals($response->getErrorMessage(), $e->getMessage());
        $this->assertEquals($response->getFieldsErrors(), $formattedErrors);
    }

    
}