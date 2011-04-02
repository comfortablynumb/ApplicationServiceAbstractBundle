<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService;

class ApplicationServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $testServiceClass1 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService';
    protected $testServiceClass2 = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationService2';
    protected $testServiceClassWithRandomID = 'ENC\Bundle\ApplicationServiceAbstractBundle\Test\ApplicationService\TestApplicationServiceWithRandomID';
    
    public function test_isValidService_returnsTrueIfItsAValidApplicationService()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create($this->testServiceClass2);

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
        $service2 = TestApplicationServiceFactory::create($this->testServiceClass2);

        $service->addService($service2);
        $service->addService($service2);
    }

    public function test_addService_whenAddingAServiceToAnotherServiceItsMarkedAsSubservice()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create($this->testServiceClass2);

        $service->addService($service2);

        $this->assertTrue($service->hasService($service2->getID()));

        $subService = $service->getService($service2->getID());

        $this->assertTrue($subService->isSubService());
    }

    public function test_getServices_returnsServicesAddedToMainService()
    {
        $service = TestApplicationServiceFactory::create();
        $subServicesArray = array(
            TestApplicationServiceFactory::create($this->testServiceClass2),
            TestApplicationServiceFactory::create($this->testServiceClassWithRandomID),
            TestApplicationServiceFactory::create($this->testServiceClassWithRandomID)
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
        $service2 = TestApplicationServiceFactory::create($this->testServiceClass2);

        $service->addService($service2);
        
        $this->assertTrue($service->hasService($service2->getID()));
        $this->assertFalse($service->hasService('someServiceID'));
    }

    public function test_setServices_setServicesAndThrowExceptionInCaseOneOfThemIsNotAValidService()
    {
        $service = TestApplicationServiceFactory::create();
        $service2 = TestApplicationServiceFactory::create($this->testServiceClassWithRandomID);

        $subServicesArray = array(
            TestApplicationServiceFactory::create($this->testServiceClass2),
            TestApplicationServiceFactory::create($this->testServiceClassWithRandomID),
            TestApplicationServiceFactory::create($this->testServiceClassWithRandomID)
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


}
