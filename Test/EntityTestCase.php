<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test;

class EntityTestCase extends WebTestCase
{
    public function runEntityValidationErrorTest(array $data, $entity, $errorMessageFormat = null)
    {
        $this->runEntityValidationTest(false, $data, $entity, $errorMessageFormat);
    }
    
    public function runEntityValidationSuccessTest(array $data, $entity, $errorMessageFormat = null)
    {
        $this->runEntityValidationTest(true, $data, $entity, $errorMessageFormat);
    }
    
    public function runEntityValidationTest($testForSuccess = false, array $data, $entity, $errorMessageFormat = null)
    {
        try {
            $constraintViolationList = $this->validator->validate($entity);
        } catch (\Exception $e) {
            $this->fail('There has been an exception while validation an entity: '.$e->getMessage());
        }
        
        foreach ($data as $field => $invalidValue) {
            $ok = $testForSuccess ? true : false;
            $errorMessage = null;
            
            foreach ($constraintViolationList as $constraintViolation) {
                $propertyPath = $constraintViolation->getPropertyPath();
                
                if ($field === $propertyPath || $field === substr($propertyPath, 0, strpos($propertyPath, '.'))) {
                    $ok = $testForSuccess ? false : true;
                    $errorMessage = is_null($errorMessageFormat) ? null : strtr($errorMessageFormat, array('%field%' => $field, '%errorMessage%' => '"'.$constraintViolation->getMessageTemplate().'"'));
                    
                    break;
                }
            }
            
            $this->assertTrue($ok, $errorMessage);
        }
    }
}