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
            if (is_array($invalidValue) && isset($invalidValue['skip']) && $invalidValue['skip'] === true) {
                continue;
            }
            
            $ok = $testForSuccess ? true : false;
            $errorMessage = $testForSuccess ? null : 'If this message is shown then it means no errors were thrown, although there were some errors expected for field "%fieldWithoutErrors%" to be thrown.';
            
            foreach ($constraintViolationList as $constraintViolation) {
                $propertyPath = $constraintViolation->getPropertyPath();
                
                if ($field === $propertyPath || $field === substr($propertyPath, 0, strpos($propertyPath, '.'))) {
                    $ok = $testForSuccess ? false : true;
                    $errorMessage = is_null($errorMessageFormat) ? null : strtr($errorMessageFormat, array('%field%' => $field, '%errorMessage%' => '"'.$constraintViolation->getMessageTemplate().'"'));
                    
                    break;
                }
            }
            
            $this->assertTrue($ok, str_replace('%fieldWithoutErrors%', $field, $errorMessage));
        }
    }
    
    public function bindDataToObject(array $data, $object)
    {
        foreach ($data as $field => $value) {
            $method = 'set'.ucfirst($field);
            
            if (method_exists($object, $method)) {
                $object->$method($value);
            }
        }
    }
}