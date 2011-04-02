<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class ApplicationServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SERVICE_CLASS1 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService';
    const TEST_SERVICE_CLASS2 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService2';
    const TEST_SERVICE_CLASS_WITH_RANDOM_ID = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationServiceWithRandomID';
    
    const TEST_ENTITY = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\Entity\TestEntity';
    const TEST_ENTITY_REPOSITORY = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\Entity\TestEntityRepository';

    const EXCEPTION_INVALID_DATA = 'ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationInvalidDataException';
    const EXCEPTION_SUB_SERVICE = 'ENC\Bundle\ApplicationServiceAbstractBundle\Exception\SubServiceException';

    public function test_isValidService_returnsTrueIfItsAValidApplicationService()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2);

        $this->assertTrue($service->isValidService($service2));
        $this->assertFalse($service->isValidService(new \DateTime()));
    }

    /**
     * @expectedException LogicException
     */
    public function test_addService_doesntLetAddAServiceToItself()
    {
        $service = TestApplicationServiceFactory::create();

        $service->addService($service);
    }    

    /**
     * @expectedException LogicException
     */
    public function test_addService_doesntLetAddAServiceToAnotherServiceThatAlreadyHasIt()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);
        $service->addService($service2);
    }

    public function test_addService_whenAddingAServiceToAnotherServiceItsMarkedAsSubservice()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);

        $this->assertTrue($service->hasService($service2->getID()));

        $subService = $service->getService($service2->getID());

        $this->assertTrue($subService->isSubService());
    }

    public function test_getServices_returnsServicesAddedToMainService()
    {
        $service = TestApplicationServiceFactory::create();
        $subServicesArray = array(
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2),
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID),
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID)
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
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2);

        $service->addService($service2);
        
        $this->assertTrue($service->hasService($service2->getID()));
        $this->assertFalse($service->hasService('someServiceID'));
    }

    public function test_setServices_setServicesAndThrowExceptionInCaseOneOfThemIsNotAValidService()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID);

        $subServicesArray = array(
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS2),
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID),
            TestApplicationServiceFactory::create(self::TEST_SERVICE_CLASS_WITH_RANDOM_ID)
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

    public function test_formatErrorsFromList_callsFormatMethodOfValidationErrorsFormatterInterfaceObject()
    {
        $service = TestApplicationServiceFactory::create();
        $formatterMock = $service->getValidationErrorsFormatter();
        $object = new \DateTime();
        $errorsList = TestApplicationServiceFactory::getConstraintViolationListMock();
        $formatForFieldName = 'entity';

        $formatterMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($object), $this->equalTo($errorsList), $this->equalTo($formatForFieldName));

        $service->formatErrorsFromList($object, $errorsList, $formatForFieldName);
    }

    public function test_validateObject_callsValidatorsValidateMethodInternally()
    {
        $service = TestApplicationServiceFactory::create();
        $validator = $service->getValidator();
        $entityClass = self::TEST_ENTITY; 
        $entity = new $entityClass;
        $constraintViolationList = TestApplicationServiceFactory::getConstraintViolationListMock();

        $constraintViolationList->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($constraintViolationList));
        
        $service->validateObject($entity);
    }

    public function test_validateObject_callsValidatorsValidateMethodInternallyAndThrowsApplicationInvalidDataInCaseOfErrors()
    {
        $service = TestApplicationServiceFactory::create();
        $validator = $service->getValidator();
        $entityClass = self::TEST_ENTITY; 
        $entity = new $entityClass;
        $constraintViolationList = TestApplicationServiceFactory::getConstraintViolationListMock();

        $constraintViolationList->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($constraintViolationList));
        
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

        $serviceMock = TestApplicationServiceFactory::createMock($methodsToMock, array(), true);

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
            ->method('notifyPreDataValidation')
            ->with($this->equalTo($object));
        $serviceMock->expects($this->once())
            ->method('validateObject')
            ->with($this->equalTo($object))
            ->will($this->returnValue($object));
        $serviceMock->expects($this->once())
            ->method('notifyPostDataValidation')
            ->with($this->equalTo($object));

        $serviceMock->bindDataToObjectAndValidate($data, $object);
    }

    public function test_handleException_ApplicationServiceExceptionInterfaceHandling()
    {
        $service = TestApplicationServiceFactory::create();

        $e = new Exception\ApplicationUnknownException('App Error');

        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationUnknownException');
        $this->assertEquals($response->getErrorMessage(), $e->getMessage());
    }

    public function test_handleException_UnknownExceptionInterfaceHandling()
    {
        $service = TestApplicationServiceFactory::create();
        $e = new \InvalidArgumentException('App Error');

        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationUnknownException');
        $this->assertEquals($response->getErrorMessage(), $e->getMessage());
    }

    public function test_handleException_SubServiceExceptionHandling()
    {
        $service = TestApplicationServiceFactory::create();
        $response = TestApplicationServiceFactory::getResponse();

        $subServiceException = new \InvalidArgumentException('Bad Argument');
        $e = new Exception\SubServiceException('App Error', 0, $subServiceException, $response);

        $service->handleException($e);

        $serviceResponse = $service->getServiceResponse();

        $this->assertEquals($serviceResponse, $response);

        $this->setExpectedException(self::EXCEPTION_SUB_SERVICE);

        $service->setIsSubService(true);

        $service->handleException($e);
    }

    public function test_handleException_ApplicationInvalidDataExceptionHandling()
    {
        $service = TestApplicationServiceFactory::create();
        $entityClass = self::TEST_ENTITY;
        $entity = new $entityClass;
        $constraintViolationList = TestApplicationServiceFactory::getConstraintViolationListMock();
        $validationErrorsFormatterMock = $service->getValidationErrorsFormatter();
        $formattedErrors = array(
            'field'     => 'error',
            'field2'    => 'error2'
        );
        $validationErrorsFormatterMock->expects($this->once())
            ->method('format')
            ->with($this->equalTo($entity), $this->equalTo($constraintViolationList), $this->equalTo(null))
            ->will($this->returnValue($formattedErrors));

        $e = new Exception\ApplicationInvalidDataException($constraintViolationList, $entity, 'Form Errors');

        $service->handleException($e);

        $response = $service->getServiceResponse();

        $this->assertFalse($response->isSuccess());
        $this->assertEquals($response->getErrorType(), 'ApplicationInvalidDataException');
        $this->assertEquals($response->getErrorMessage(), $e->getMessage());
        $this->assertEquals($response->getFieldsErrors(), $formattedErrors);
    }

    
}
