<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder\ODM\MongoDB;

use Doctrine\ORM\QueryBuilder;

use ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder\FinderQueryBuilderAbstract;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class FinderQueryBuilder extends FinderQueryBuilderAbstract
{
    public function create(array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null)
    {
        $metadata = $this->getEntityClassMetadata();
        $validFieldsForOrder = $this->getValidFieldsForOrder();
        
        if (is_null($qb)) {
            $qb = $this->createQueryBuilder();
        }

        $qb = $this->walkFilters($qb, $filters);
        
        $orderBy = $validFieldsForOrder[$orderBy];
        
        $qb->sort($orderBy, $orderType);
        
        if ($onlyCount === false) {
            if (!is_null($limit) && $limit > 0) {
                $qb->limit($limit);
                
                if (!is_null($start)) {
                    $qb->skip($start);
                }
            }
        } else {
            $qb->limit(null);
            $qb->skip(null);
        }
        
        return $qb;
    }
    
    public function walkFilters($qb, array $filters = array())
    {
        $filterOperators = $this->getFilterOperators();
        
        foreach ($filters as $filterOperator => $data) {
            $qb = $this->applyFilterOperator($qb, $filterOperator, $data);
        }

        return $qb;
    }
    
    public function applyFilterOperator($qb, $filterOperator, $data)
    {
        switch ($filterOperator) {
            case self::FILTER_GREATER_THAN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyGreaterThanOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_GREATER_THAN_OR_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyGreaterThanOrEqualToOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LESS_THAN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyLessThanOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LESS_THAN_OR_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyLessThanOrEqualToOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyEqualToOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_NOT_EQUAL_TO:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyNotEqualToOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_LIKE:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $value) {
                        $this->validateField($field);
                        $value = $this->castValueForFieldType($field, $value);
                        
                        $qb = $this->applyLikeOperator($qb, $field, $value);
                    }
                }
                
                break;
            case self::FILTER_IN:
                foreach ($data as $index => $fieldsAndValues) {
                    foreach ($fieldsAndValues as $field => $arrayOfValues) {
                        if (!is_array($arrayOfValues)) {
                            if (strpos($arrayOfValues, $this->delimiterForStringRepresentingArray) === false) {
                                $arrayOfValues = array($arrayOfValues);
                            } else {
                                $arrayOfValues = explode($this->delimiterForStringRepresentingArray, $arrayOfValues);
                            }
                        }
                        
                        $this->validateField($field);
                        
                        foreach ($arrayOfValues as $index => $value) {
                            $arrayOfValues[$index] = $this->castValueForFieldType($field, $value);
                        }
                        
                        $qb = $this->applyInOperator($qb, $field, $arrayOfValues);
                    }
                }
                
                break;
            default:
                throw new Exception\FilterQueryBuilder\InvalidFilterOperatorException(sprintf('El operador de filtro "%s" es inválido.', $filterOperator));
                
                break;
        }
        
        return $qb;
    }

    public function castValueForFieldType($field, $value)
    {
        $metadata = $this->getEntityClassMetadata();
        $field = strpos($field, '.') !== false ? substr($field, 0, strpos($field, '.')) : $field;
        $fieldMapping = $metadata->getFieldMapping($field);
        
        switch ($fieldMapping['type']) {
            case 'boolean':
                $value = $value === 'false' || $value === 0 || $value === '0' || $value === 'off' ? false : true;

                break;
            case 'timestamp':
                $value = new \MongoTimestamp(strtotime($value));

                break;
            case 'int':
                $value = (int) $value;
                
                break;
            case 'float':
                $value = (float) $value;
                
                break;
            case 'date':
                $value = new \MongoDate(strtotime($value));

                break;
            default:
                break;
        }

        return $value;
    }

    public function applyGreaterThanOperator($qb, $field, $value)
    {
        $qb->field($field)->gt($value);
        
        return $qb;
    }
    
    public function applyGreaterThanOrEqualToOperator($qb, $field, $value)
    {
        $qb->field($field)->gte($value);
        
        return $qb;
    }
    
    public function applyLessThanOperator($qb, $field, $value)
    {
        $qb->field($field)->lt($value);
        
        return $qb;
    }
    
    public function applyLessThanOrEqualToOperator($qb, $field, $value)
    {
        $qb->field($field)->lte($value);
        
        return $qb;
    }
    
    public function applyEqualToOperator($qb, $field, $value)
    {
        $qb->field($field)->equals($value);
        
        return $qb;
    }
    
    public function applyNotEqualToOperator($qb, $field, $value)
    {
        $qb->field($field)->notEqual($value);
        
        return $qb;
    }
    
    public function applyLikeOperator($qb, $field, $value)
    {
        $qb->field($field)->equals('/'.$value.'/');
        
        return $qb;
    }
    
    public function applyInOperator($qb, $field, $arrayOfValues)
    {
        $qb->field($field)->in($arrayOfValues);
        
        return $qb;
    }
    
    public function createQueryBuilder()
    {
        return $this->getPersistenceManager()->createQueryBuilder($this->getEntityClass());
    }
}

