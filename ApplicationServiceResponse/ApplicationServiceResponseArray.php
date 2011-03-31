<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

class ApplicationServiceResponseArray extends ApplicationServiceResponse
{
    public function __construct( array $data = array() )
    {
        $this->setData( $data );
    }
    
    public function getResponse()
    {
        return $this->data;
    }
    
    public function addFieldError( $field, $error )
    {
        $this->setIsSuccess( false );
        
        $this->data[ 'errors' ][ $field ] = $error;
    }
        
    public function getErrorMessage()
    {
        return $this->isSuccess() === false ? $this->data[ 'msg' ] : '';
    }
    
    public function getErrorType()
    {
        return $this->isSuccess() === false && isset( $this->data[ 'error_type' ] ) ? $this->data[ 'error_type' ] : '';
    }
    
    public function getFieldError( $field )
    {
        if ( $this->hasFieldError( $field ) )
        {
            return $this->data[ 'errors' ][ $field ];
        }
        else
        {
            return false;
        }
    }
    
    public function getFieldsErrors()
    {
        return $this->isSuccess() === false && isset( $this->data[ 'errors' ] ) ? $this->data[ 'errors' ] : array();
    }
    
    public function getPartialCount()
    {
        return isset( $this->data[ 'partialCount' ] ) ? $this->data[ 'partialCount' ] : '';
    }
    
    public function getRow()
    {
        return isset( $this->data[ 'row' ] ) ? $this->data[ 'row' ] : array();
    }
    
    public function getRowObject()
    {
        return $this->rowObject;
    }
    
    public function getRows()
    {
        return isset( $this->data[ 'rows' ] ) ? $this->data[ 'rows' ] : array();
    }
    
    public function getSuccessMessage()
    {
        return $this->isSuccess() === true ? $this->data[ 'msg' ] : '';
    }
    
    public function getTotalCount()
    {
        return isset( $this->data[ 'totalCount' ] ) ? $this->data[ 'totalCount' ] : '';
    }
    
    public function hasFieldError( $field )
    {
        if ( $this->isSuccess() === false && isset( $this->data[ 'errors' ][ $field ] ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function isSuccess()
    {
        return $this->data[ 'success' ];
    }
    
    public function removeFieldError( $field )
    {
        if ( $this->hasFieldError( $field ) )
        {
            unset( $this->data[ 'errors' ][ $field ] );
        }
    }
    
    public function setData( array $data )
    {
        $keys = array_keys( $data );
        
        if ( !in_array( 'success', $keys ) )
        {
            $data[ 'success' ] = '';
        }
        
        if ( !in_array( 'msg', $keys ) )
        {
            $data[ 'msg' ] = '';
        }
        
        $this->data = $data;
    }
    
    public function setErrorMessage( $errorMessage )
    {
        $this->setIsSuccess( false );
        
        $this->data[ 'msg' ] = $errorMessage;
    }
    
    public function setErrorType( $errorType )
    {
        $this->setIsSuccess( false );
        
        $this->data[ 'error_type' ] = $errorType;
    }
    
    public function setFieldsErrors( array $errors )
    {
        $this->setIsSuccess( false );
        
        $this->data[ 'errors' ] = $errors;
    }
    
    public function setIsSuccess( $isSuccess )
    {
        if ( !is_bool( $isSuccess ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El primer argumento debe ser un boolean. Se recibio: "%s".', $isSuccess ) );
        }
        
        if ( $isSuccess === true )
        {
            unset( $this->data[ 'error_type' ] );
            unset( $this->data[ 'errors' ] );
        }
        else
        {
            $this->data[ 'error_type' ] = !isset( $this->data[ 'error_type' ] ) ? '' : $this->data[ 'error_type' ];
            $this->data[ 'errors' ]     = !isset( $this->data[ 'errors' ] ) ? array() : $this->data[ 'errors' ];
        }
        
        $this->data[ 'success' ] = $isSuccess;
    }
    
    public function setPartialCount( $partialCount )
    {
        if ( !is_int( $partialCount ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El primer argumento debe ser un numero entero. Se recibio: "%s".', $partialCount ) );
        }
        
        $this->data[ 'partialCount' ] = $partialCount;
    }
    
    public function setRow( array $row )
    {
        $this->data[ 'row' ] = $row;
    }
    
    public function setRowObject( $rowObject )
    {
        $this->rowObject = $rowObject;
    }
    
    public function setRows( array $rows )
    {
        $this->data[ 'rows' ] = $rows;
    }
    
    public function setSuccessMessage( $successMessage )
    {
        $this->setIsSuccess( true );
        
        $this->data[ 'msg' ] = $successMessage;
    }
    
    public function setTotalCount( $totalCount )
    {
        if ( !is_int( $totalCount ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El primer argumento debe ser un numero entero. Se recibio: "%s".', $totalCount ) );
        }
        
        $this->data[ 'totalCount' ] = $totalCount;
    }
}
