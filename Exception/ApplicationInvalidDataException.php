<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationList;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception\ApplicationServiceExceptionInterface;

class ApplicationInvalidDataException extends ApplicationException
{
    protected $errorList;
    protected $entity;
    
    public function __construct( ConstraintViolationList $list, $entity, $message = '' )
    {
        parent::__construct( $message );
        
        if ( !is_object( $entity ) )
        {
            throw new \InvalidArgumentException( sprintf( 'El segundo argumento debe ser la entidad que disparo el error de validacion. Se recibio: "%s".', $entity ) );
        }
        
        $this->errorList    = $list;
        $this->entity       = $entity;
    }
    
    public function getErrorList()
    {
        return $this->errorList;
    }
    
    public function getEntity()
    {
        return $this->entity;
    }
    
    public function getFriendlyMessage()
    {
        return 'Uno o mas de los valores enviados son invalidos.';
    }
    
    public function getType()
    {
        return 'ApplicationInvalidDataException';
    }
}
