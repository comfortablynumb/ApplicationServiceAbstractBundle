<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Composite;

use ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder\FinderQueryBuilderAbstract;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class FinderQueryBuilder extends FinderQueryBuilderAbstract
{
    const FILTER_INSTANCE_OF    = ':INSTANCE_OF:';
    const FILTER_ALL_FIELDS     = ':ALL_FIELDS:';
    const FILTER_AND            = ':AND:';
    const FILTER_OR             = ':OR:';
    const FILTER_MEMBER_OF      = ':MEMBER_OF:';
    const FILTER_IS_NULL        = ':IS_NULL:';

    public function setFilterOperators()
    {
        parent::setFilterOperators();

        $this->filterOperators[] = self::FILTER_INSTANCE_OF;
        $this->filterOperators[] = self::FILTER_ALL_FIELDS;
        $this->filterOperators[] = self::FILTER_AND;
        $this->filterOperators[] = self::FILTER_OR;
        $this->filterOperators[] = self::FILTER_MEMBER_OF;
        $this->filterOperators[] = self::FILTER_IS_NULL;
    }

    public function create(array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null)
    {
        $entityClass = $this->getEntityClass();
        $selectDqlPart = $qb->getDqlPart('select');
        $fromDqlPart = $qb->getDqlPart('from');
        $actualRootAlias = $this->getAliasForEntityFromDql($qb, $entityClass);
        $rootAlias = $actualRootAlias !== false ? $actualRootAlias : $this->getEntityDqlAlias();
        $metadata = $this->getEntityClassMetadata();
        $validFieldsForOrder = $this->getValidFieldsForOrder();
        
        if ($onlyCount === true) {
            $qb->select(sprintf('COUNT( %s )', $rootAlias));
        } else if (empty($selectDqlPart)) {
            $qb->select($rootAlias);
        }
        
        if (empty($fromDqlPart)) {
            $qb->from($entityClass, $rootAlias);
        }
        
        $expressions = $this->walkFilters($qb, $filters);
        
        if (!empty($expressions)) {
            foreach ($expressions as $expression) {
                if (is_string($expression) && trim($expression) === '') {
                    continue;
                }
                
                $qb->andWhere($expression);
            }
        }
        
        $orderBy = $validFieldsForOrder[$orderBy];
        $orderBy = strpos($orderBy, '.') === false ? $rootAlias.'.'.$orderBy : $orderBy;
        
        $qb->addOrderBy($orderBy, $orderType);
        
        if ($onlyCount === false) {
            if (!is_null($limit) && $limit > 0) {
                $qb->setMaxResults($limit);
                
                if (!is_null($start)) {
                    $qb->setFirstResult($start);
                }
            }
        } else {
            $qb->setFirstResult(null);
            $qb->setMaxResults(null);
        }
        
        return $qb;
    }
    
    public function walkFilters($qb, array $filters = array())
    {
        $filterOperators = $this->getFilterOperators();
        $expressions = array();
        
        foreach ($filters as $filterOperator => $data) {
            $expressions = array_merge($this->getExpressionsForFilterOperator($qb, $filterOperator, $data), $expressions);
        }

        return $expressions;
    }

    public function getExpressionsForFilterOperator($qb, $filterOperator, $data)
    {
        $expressions = array();
        
        switch ($filterOperator) {
            case self::FILTER_GREATER_THAN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getGreaterThanExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_GREATER_THAN_OR_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getGreaterThanOrEqualToExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LESS_THAN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getLessThanExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LESS_THAN_OR_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getLessThanOrEqualToExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getEqualToExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_NOT_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getNotEqualToExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LIKE:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $expressions[] = $this->getLikeExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_IN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $arrayOfValues) {
                        $this->validateField($field);
                        
                        if (!is_array($arrayOfValues)) {
                            if (strpos($arrayOfValues, $this->delimiterForStringRepresentingArray) === false) {
                                $arrayOfValues = array($arrayOfValues);
                            } else {
                                $arrayOfValues = explode($this->delimiterForStringRepresentingArray, $arrayOfValues);
                            }
                        }
                        
                        foreach ($arrayOfValues as $index => $value) {
                            $arrayOfValues[$index] = $this->castValueForFieldType($field, $value);
                        }
                        
                        $expressions[] = $this->getInExpression($qb, $field, $arrayOfValues);
                    }
                }
                
                break;
            case self::FILTER_INSTANCE_OF:
                foreach ($data as $index => $class) {
                    $class = (string) $class;
                        
                    $expressions[] = $this->getInstanceOfExpression($qb, $class);
                }
                
                break;
            case self::FILTER_MEMBER_OF:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                    
                        $expressions[] = $this->getMemberOfExpression($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_ALL_FIELDS:
                $expressions[] = $this->getAllFieldsExpression($qb, $data);
                
                break;
            case self::FILTER_AND:
                foreach ($data as $index => $subQueryData) {
                    $expressions[] = $this->getAndExpression($qb, $data);
                }
                
                break;
            case self::FILTER_OR:
                foreach ($data as $index => $subQueryData) {
                    $expressions[] = $this->getOrExpression($qb, $data);
                }
                
                break;
            case self::FILTER_IS_NULL:
                foreach ($data as $index => $field) {
                    $this->validateField($field);
                        
                    $expressions[] = $this->getIsNullExpression($qb, $field);
                }
                
                break;
            default:
                throw new Exception\FinderQueryBuilder\InvalidFilterOperatorException(sprintf('El operador de filtro "%s" es inválido.', $filterOperator));
                
                break;
        }
        
        return $expressions;
    }

    public function castValueForFieldType($field, $value)
    {
        if (strpos($field, '.') === false) {
            $metadata = $this->getEntityClassMetadata();
            $fieldMapping = $metadata->getFieldMapping($field);
            
            switch ($fieldMapping['type']) {
                case 'boolean':
                    $value = $value === 'false' || $value === 0 || $value === '0' || $value === 'off' ? false : true;

                    break;
                case 'integer':
                case 'bigint':
                case 'smallint':
                    $value = (int) $value;
                    
                    break;
                case 'decimal':
                    $value = (double) $value;

                    break;
                case 'float':
                    $value = (float) $value;

                    break;
                default:
                    break;
            }
        }
        
        return $value;
    }

    public function getGreaterThanExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->gt($field, $qb->expr()->literal($value));
    }
    
    public function getGreaterThanOrEqualToExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->gte($field, $qb->expr()->literal($value));
    }
    
    public function getLessThanExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->lt($field, $qb->expr()->literal($value));
    }
    
    public function getLessThanOrEqualToExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->lte($field, $qb->expr()->literal($value));
    }
    
    public function getEqualToExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->eq($field, $qb->expr()->literal($value));
    }
    
    public function getNotEqualToExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->neq($field, $qb->expr()->literal($value));
    }
    
    public function getInstanceOfExpression($qb, $class)
    {
        return sprintf( '%s INSTANCE OF %s', $this->getEntityDqlAlias(), $qb->expr()->literal($class) );
    }
    
    public function getMemberOfExpression($qb, $field, $value)
    {
        return sprintf( '%s MEMBER OF %s.%s', $qb->expr()->literal($value), $this->getEntityDqlAlias(), $field );
    }
    
    public function getLikeExpression($qb, $field, $value)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;

        return $qb->expr()->like($field, $qb->expr()->literal('%'.$value.'%'));
    }
    
    public function getAndExpression($qb, $subQueryData)
    {
        if (!is_array( $subQueryData )) {
            throw new Exception\FinderQueryBuilder\InvalidDataForFilterOperatorException(sprintf( 'El operador "%s" requiere un array de arrays como valor. Se recibió: "%s".', self::FILTER_AND, $subQueryData));
        }
        
        $expr = $qb->expr()->andx();
        
        foreach ($subQueryData as $data) {
            $expr->addMultiple($this->walkFilters($qb, $data));
        }
        
        return '('.$expr->__toString().')';
    }
    
    public function getOrExpression($qb, $subQueryData)
    {
        if (!is_array( $subQueryData )) {
            throw new Exception\FinderQueryBuilder\InvalidDataForFilterOperatorException(sprintf( 'El operador "%s" requiere un array de arrays como valor. Se recibió: "%s".', self::FILTER_OR, $subQueryData));
        }
        
        $expr = $qb->expr()->orx();
        
        foreach ($subQueryData as $data) {
            $expr->addMultiple($this->walkFilters($qb, $data));
        }
        
        return '('.$expr->__toString().')';
    }
    
    public function getInExpression($qb, $field, $arrayOfValues)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return $qb->expr()->in($field, $arrayOfValues);
    }

    public function getAllFieldsExpression($qb, $value)
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        $expr = $qb->expr()->orx();
                
        foreach ($this->getValidFieldsForSearch() as $field) {
            $expr->add($this->getLikeExpression($qb, $field, $value));
        }

        return $expr;
    }
    
    public function getIsNullExpression($qb, $field)
    {
        $field = strpos($field, '.') === false ? $this->getEntityDqlAlias().'.'.$field : $field;
        
        return sprintf('%s.%s IS NULL', $field);
    }
    
    public function createQueryBuilder()
    {
        return $this->getPersistenceManager()->createQueryBuilder();
    }
   
    public function getAliasForEntityFromDql($qb)
    {
        $fromDqlPart = $qb->getDqlPart('from');
        
        foreach ($fromDqlPart as $fromPart) {
            if ($fromPart->getFrom() === $this->getEntityClass()) {
                return $fromPart->getAlias();
            }
        }
        
        return false;
    } 
}
