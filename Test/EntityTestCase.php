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
        $constraintViolationList = $this->validator->validate($entity);
        
        foreach ($data as $field => $invalidValue) {
            $ok = $testForSuccess ? true : false;
            
            foreach ($constraintViolationList as $constraintViolation) {
                $propertyPath = $constraintViolation->getPropertyPath();
                
                if ($field === $propertyPath || $field === substr($propertyPath, 0, strpos($propertyPath, '.'))) {
                    $ok = $testForSuccess ? false : true;
                    
                    break;
                }
            }
            
            $this->assertTrue($ok, is_null($errorMessageFormat) ? null : str_replace('%field%', $field, $errorMessageFormat));
        }
    }
}