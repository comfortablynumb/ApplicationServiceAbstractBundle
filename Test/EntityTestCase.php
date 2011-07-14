<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test;

class EntityTestCase extends TestCase
{
    public function runEntityValidationTests(array $data, $entity, $errorMessageFormat = null)
    {
        $constraintViolationList = $this->validator->validate($entity);
        
        foreach ($data as $field => $invalidValue) {
            $ok = false;
            
            foreach ($constraintViolationList as $constraintViolation) {
                $propertyPath = $constraintViolation->getPropertyPath();
                
                if ($field === $propertyPath || $field === substr($propertyPath, 0, strpos($propertyPath, '.'))) {
                    $ok = true;
                    
                    break;
                }
            }
            
            $this->assertTrue($ok, is_null($errorMessageFormat) ? null : str_replace('%field%', $field, $errorMessageFormat));
        }
    }
}