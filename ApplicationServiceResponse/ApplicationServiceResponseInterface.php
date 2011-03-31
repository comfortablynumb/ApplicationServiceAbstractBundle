<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationServiceResponse;

interface ApplicationServiceResponseInterface
{
	public function __construct( array $data = array() );
	public function addFieldError( $field, $errorMessage );
	public function getData();
	public function getErrorMessage();
	public function getErrorType();
	public function getFieldError( $field );
	public function getFieldsErrors();
	public function getPartialCount();
	public function getResponse();
	public function getRow();
	public function getRowObject();
	public function getRows();
	public function getSuccessMessage();
	public function getTotalCount();
	public function hasFieldError( $field );
	public function isSuccess();
	public function removeFieldError( $field );
	public function setData( array $data );
	public function setErrorMessage( $errorMessage );
	public function setErrorType( $errorType );
	public function setFieldsErrors( array $fieldsErrors );
	public function setIsSuccess( $isSuccess );
	public function setPartialCount( $partialCount );
	public function setRow( array $row );
	public function setRowObject( $row );
	public function setRows( array $rows );
	public function setSuccessMessage( $successMessage );
	public function setTotalCount( $totalCount );
}