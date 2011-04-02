<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationErrorsFormatter implements ValidationErrorsFormatterInterface
{
    public function format($object, ConstraintViolationList $errorList, $formatForFieldName = null)
    {
        $result = array();
            
        foreach ( $errorList as $error )
        {
            $messageParameters  = $error->getMessageParameters();
            
            if ( isset( $messageParameters[ 'errorType' ] ) && $messageParameters[ 'errorType' ] === 'Unique' )
            {
                $properties = array();
                
                if ( strpos( $messageParameters[ 'properties' ], '|' ) !== false )
                {
                    $properties = explode( '|', $messageParameters[ 'properties' ] );
                }
                else
                {
                    $properties[] = $messageParameters[ 'properties' ];
                }
                
                foreach ( $properties as $property )
                {
                    $fieldName              = str_replace( get_class( $object ).'.', '', $error->getPropertyPath() );
                    $fieldName              = $fieldName === '' ? $property : $fieldName;
                    $fieldName              = is_null( $formatForFieldName ) ? $fieldName : $formatForFieldName.'['.str_replace( '.', '][', $fieldName ).']';
                    
                    $result[ $fieldName ]   = $error->getMessage();
                }
            }
            else
            {
                $fieldName              = str_replace( get_class( $object ).'.', '', $error->getPropertyPath() );
                
                // Necesario para que los "getters" validation methods mapeen correctamente los campos con error
                $fieldName              = strpos( $fieldName, 'FieldValid' ) !== false ? str_replace( 'FieldValid', '', $fieldName ) : $fieldName;
                $fieldName              = is_null( $formatForFieldName ) ? $fieldName : $formatForFieldName.'['.str_replace( '.', '][', $fieldName ).']';
                
                $result[ $fieldName ]   = $error->getMessage();
            }
        }
        
        return $result;
    }
}
