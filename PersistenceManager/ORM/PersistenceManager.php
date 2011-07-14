<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\ORM;

use Doctrine\ORM\EntityManager;

use ENC\Bundle\ApplicationServiceAbstractBundle\PersistenceManager\PersistenceManagerAbstract;
use ENC\Bundle\ApplicationServiceAbstractBundle\Exception;

class PersistenceManager extends PersistenceManagerAbstract
{
    public function __construct(EntityManager $em)
    {
        $this->setPersistenceManager($em);
    }
    
    public function setPersistenceManager(EntityManager $entityManager)
    {
        $this->pm = $entityManager;
    }
    
    public function createQuery($dql)
    {
        return $this->pm->createQuery($dql);
    }

    public function createQueryBuilder($document = null)
    {
        return $this->pm->createQueryBuilder($document);
    }

    public function lock($object, $lockMode, $lockVersion)
    {
        try {
            $this->pm->lock($object, $lockMode, $lockVersion);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            $msg = 'Ocurrio un error de concurrencia en la base de datos. Probablemente otra persona envio sus modificaciones sobre la entidad antes de que usted las envie. Por favor, realice sus cambios nuevamente.';
            
            throw new Exception\DatabaseConcurrencyException($msg, $e->getCode(), $e);
        } catch(\Doctrine\ORM\PessimisticLockException $e) {
            $msg = 'Alguien requirio un bloqueo exclusivo para una de las entidades que usted desea modificar.';
            
            throw new Exception\DatabaseConcurrencyException($msg, $e->getCode(), $e);
        } catch(\Exception $e) {
            $msg = 'Ocurrio un error desconocido en la aplicacion.';
            
            throw new Exception\ApplicationUnknownException($msg, $e->getCode(), $e);
        }
    }
    
    public function flush(array $options = array())
    {
        try {
            $this->pm->flush($options);
        } catch(\Doctrine\ORM\OptimisticLockException $e) {
            $msg = 'Ocurrio un error de concurrencia en la base de datos. Probablemente otra persona envio sus modificaciones sobre la entidad antes de que usted las envie. Por favor, realice sus cambios nuevamente.';
            
            throw new Exception\DatabaseConcurrencyException($msg, (int) $e->getCode(), $e);
        } catch(\Doctrine\ORM\PessimisticLockException $e) {
            $msg = 'Alguien requirió un bloqueo exclusivo para una de las entidades que usted desea modificar.';
            
            throw new Exception\DatabaseConcurrencyException($msg, (int) $e->getCode(), $e);
        } catch(\PDOException $e) {
            if ($e->getCode() === '23000')
            {
                // Determinamos el codigo del error de la BD
                $code = $this->extractSqlErrorCodeFromMessage($e->getMessage());
                
                switch ($code) {
                    case '1451':
                        $msg = 'No se puede eliminar el elemento porque existe/n otro/s elemento/s haciendo referencia a él. ';
                        $msg .= 'Si aún desea proseguir, por favor, primero elimine el/los elemento/s asociado/s antes de intentar eliminar la entidad seleccionada.';
                    
                        throw new Exception\DatabaseConstraintException($msg, (int) $e->getCode(), $e);
                        
                        break;
                    default:
                        throw new Exception\DatabaseUnknownException('Se produjo un error desconocido en la Base de Datos.', (int) $e->getCode(), $e);
                        
                        break;
                }
            } else {
                throw new Exception\DatabaseUnknownException('Se produjo un error desconocido en la Base de Datos.', (int) $e->getCode(), $e);
            }
        } catch(\Exception $e) {
            $msg = 'Ocurrio un error desconocido en la aplicacion.';
            
            throw new Exception\ApplicationUnknownException($msg, (int) $e->getCode(), $e);
        }
    }

    // Utility Methods
    public function extractSqlErrorCodeFromMessage($message)
    {
        $results = array();
                
        preg_match('/SQLSTATE\[[0-9]+\]: [a-zA-Z0-9\s]+: ([0-9]{4})/', $message, $results);
        
        return is_array($results) && count($results) > 1 ? $results[1] : '';
    }

    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
    }
    
    public function commitTransaction()
    {
        $this->getConnection()->commit();
    }
    
    public function rollbackTransaction()
    {
        $this->getConnection()->rollback();
    }
    
    public function isTransactionActive()
    {
        return $this->getConnection()->isTransactionActive();
    }
}


