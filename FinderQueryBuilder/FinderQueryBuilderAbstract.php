<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder;

use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;
use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerInterface;
use ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder\FinderQueryBuilderInterface;

abstract class FinderQueryBuilderAbstract implements FinderQueryBuilderInterface
{
    // Finder Operators
    const FILTER_NOT_EQUAL_TO                   = ':!=:';
    const FILTER_LIKE                           = ':LIKE:';
    const FILTER_GREATER_THAN                   = ':>:';
    const FILTER_GREATER_THAN_OR_EQUAL_TO       = ':>=:';
    const FILTER_LESS_THAN                      = ':<:';
    const FILTER_LESS_THAN_OR_EQUAL_TO          = ':<=:';
    const FILTER_EQUAL_TO                       = ':=:';
    const FILTER_AND                            = ':AND:';
    const FILTER_OR                             = ':OR:';
    const FILTER_IN                             = ':IN:';
    
    protected $persistenceManager;
    protected $entityClass;
    protected $entityDqlAlias;
    protected $validFieldsForSearch = array();
    protected $validFieldsForOrder = array();
    protected $filterOperators = array();
    protected $delimiterForStringRepresentingArray = ',';

    public function __construct(PersistenceManagerInterface $persistenceManager, $entityClass, $entityDqlAlias, array $validFieldsForSearch = array(), array $validFieldsForOrder = array(), $delimiterForStringRepresentingArray = ',' )
    {
        $this->setPersistenceManager($persistenceManager);
        $this->setEntityClass($entityClass);
        $this->setEntityDqlAlias($entityDqlAlias);
        $this->setValidFieldsForSearch($validFieldsForSearch);
        $this->setValidFieldsForOrder($validFieldsForOrder);
        
        $this->delimiterForStringRepresentingArray = $delimiterForStringRepresentingArray;
        
        $this->setFilterOperators();
    }

    public function setPersistenceManager(PersistenceManagerInterface $entityManager)
    {
        $this->persistenceManager = $entityManager;
    }

    public function getPersistenceManager()
    {
        return $this->persistenceManager;
    }
    
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }
    
    public function setEntityDqlAlias($entityDqlAlias)
    {
        $this->entityDqlAlias = $entityDqlAlias;
    }

    public function getEntityDqlAlias()
    {
        return $this->entityDqlAlias;
    }
    
    public function setValidFieldsForSearch(array $validFieldsForSearch)
    {
        $this->validFieldsForSearch = $validFieldsForSearch;
    }

    public function getValidFieldsForSearch()
    {
        return $this->validFieldsForSearch;
    }
    
    public function setValidFieldsForOrder(array $validFieldsForOrder)
    {
        $this->validFieldsForOrder = $validFieldsForOrder;
    }

    public function getValidFieldsForOrder()
    {
        return $this->validFieldsForOrder;
    }

    public function setFilterOperators()
    {
        $this->filterOperators = array(
            self::FILTER_GREATER_THAN,
            self::FILTER_GREATER_THAN_OR_EQUAL_TO,
            self::FILTER_LESS_THAN,
            self::FILTER_LESS_THAN_OR_EQUAL_TO,
            self::FILTER_EQUAL_TO,
            self::FILTER_NOT_EQUAL_TO,
            self::FILTER_LIKE,
            self::FILTER_IN
        );
    }
     
    public function getFilterOperators()
    {
        return $this->filterOperators;
    }
    
    public function validateField($field)
    {
        if (!in_array($field, $this->getValidFieldsForSearch())) {
            throw new Exception\DatabaseInvalidFieldException(sprintf('El campo "%s" ingresado para la busqueda es invalido.', $field));
        }
        
        return true;
    }
    
    public function getEntityClassMetadata()
    {
        return $this->getPersistenceManager()->getClassMetadata($this->getEntityClass());
    }
    
    abstract function castValueForFieldType($field, $data);
}
