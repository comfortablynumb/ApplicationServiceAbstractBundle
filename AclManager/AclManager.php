<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\AclManager;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Voter\FieldVote;

class AclManager
{
    protected $aclProvider = null;
    
    public function __construct( $aclProvider )
    {
        $this->setAclProvider( $aclProvider );
    }
    
    public function getAclProvider()
    {
        return $this->aclProvider;
    }
    
    public function setAclProvider( $aclProvider )
    {
        $this->aclProvider = $aclProvider;
    }

    public function createObjectIdentityWithID($entity, $id)
    {
        return new ObjectIdentity($id, (is_object($entity) ? get_class($entity) : $entity));
    }
    
    public function createObjectIdentity( $entity, $class = null )
    {
        if (is_null($class)) {
            return ObjectIdentity::fromDomainObject( $entity );
        } else {
            return new ObjectIdentity($entity->getId(), $class);
        }
    }
    
    public function createObjectIdentityForClass($class)
    {
        return new ObjectIdentity(sha1($class), $class);
    }

    public function createFieldVoteForObject($object, $field)
    {
        return new FieldVote($object, $field);
    }
    
    public function createUserSecurityIdentity($user)
    {
        return new UserSecurityIdentity($user->getUsername(), get_class($user));
    }
    
    public function createMaskBuilder()
    {
        return new MaskBuilder();
    }
    
    public function createAcl( ObjectIdentity $objectIdentity )
    {
        return $this->getAclProvider()->createAcl( $objectIdentity );
    }
    
    public function updateAcl( $acl )
    {
        return $this->getAclProvider()->updateAcl( $acl );
    }

    public function deleteAcl( $acl )
    {
        return $this->getAclProvider()->deleteAcl( $acl );
    }
    
    public function findAcl( ObjectIdentity $oid, array $sid )
    {
        return $this->getAclProvider()->findAcl( $oid, $sid );
    }

    public function getACEFromList($granting, array $list, $sid, $field = null)
    {
        foreach ($list as $ace) {
            if ($ace->getSecurityIdentity()->equals($sid) && $ace->isGranting() === $granting) {
                if (is_null($field) || $field === $ace->getField()) {

                    return $ace;
                }
            }
        }

        return false;
    }

    public function getAllowACEFromList(array $list, $sid, $field = null)
    {
        return $this->getACEFromList(true, $list, $sid, $field);
    }

    public function getDenyACEFromList(array $list, $sid, $field = null)
    {
        return $this->getACEFromList(false, $list, $sid, $field);
    }

    public function hasACEPermission($ace, $permission)
    {
        if (!is_int($permission)) {
            throw new \InvalidArgumentException('Argument "permission" must be an integer.');
        }
        
        return ($ace->getMask() & $permission) !== 0;
    }

    public function insertClassACE( $acl, $securityIdentity, $mask, $index, $granting, $strategy = null )
    {
        $acl->insertClassACE( $securityIdentity, $mask, $index, $granting, $strategy );

        return $acl;
    }

    public function insertClassFieldACE( $acl, $field, $securityIdentity, $mask, $index, $granting, $strategy = null )
    {
        $acl->insertClassFieldACE( $field, $securityIdentity, $mask, $index, $granting, $strategy );

        return $acl;
    }

    public function insertObjectACE( $acl, $securityIdentity, $mask, $index, $granting, $strategy = null )
    {
        $acl->insertObjectACE( $securityIdentity, $mask, $index, $granting, $strategy );

        return $acl;
    }

    public function insertObjectFieldACE( $acl, $field, $securityIdentity, $mask, $index, $granting, $strategy = null )
    {
        $acl->insertObjectFieldACE( $field, $securityIdentity, $mask, $index, $granting, $strategy );

        return $acl;
    }

    public function updateClassACE( $acl, $ace, $mask, $sid )
    {
        $classACEs = $acl->getClassACEs();
        $countACEs = count( $classACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getSecurityIdentity()->equals($sid) && $ace->getId() === $classACEs[ $i ]->getId() )
            {
                $acl->updateClassACE( $i, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateClassFieldACE( $acl, $ace, $field, $mask, $sid )
    {
        $classFieldACEs = $acl->getClassFieldACEs();
        $countACEs = count( $classFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getSecurityIdentity()->equals($sid) && $ace->getId() === $classFieldACEs[ $i ]->getId() )
            {
                $acl->updateClassFieldACE( $i, $field, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateObjectACE( $acl, $ace, $mask, $sid )
    {
        $objectACEs = $acl->getObjectACEs();
        $countACEs = count( $objectACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getSecurityIdentity()->equals($sid) && $ace->getId() === $objectACEs[ $i ]->getId() )
            {
                $acl->updateObjectACE( $i, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateObjectFieldACE( $acl, $ace, $field, $mask, $sid )
    {
        $objectFieldACEs = $acl->getObjectFieldACEs($field);
        $countACEs = count( $objectFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getSecurityIdentity()->equals($sid) && $ace->getId() === $objectFieldACEs[ $i ]->getId() )
            {
                $acl->updateObjectFieldACE( $i, $field, $mask );

                break;
            }
        }

        return $acl;
    }

    public function deleteClassACE( $acl, $ace )
    {
        $classACEs  = $acl->getClassACEs();
        $countACEs  = count( $classACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $classACEs[ $i ]->getId() )
            {
                $acl->deleteClassACE( $i );

                break;
            }
        }

        return $acl;
    }

    public function deleteClassFieldACE( $acl, $ace, $field )
    {
        $classFieldACEs = $acl->getClassFieldACEs( $field );
        $countACEs      = count( $classFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $classFieldACEs[ $i ]->getId() )
            {
                $acl->deleteClassFieldACE( $i, $field );

                break;
            }
        }

        return $acl;
    }

    public function deleteObjectACE( $acl, $ace )
    {
        $objectACEs = $acl->getObjectACEs();
        $countACEs = count( $objectACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $objectACEs[ $i ]->getId() )
            {
                $acl->deleteObjectACE( $i );

                break;
            }
        }

        return $acl;
    }

    public function deleteObjectFieldACE( $acl, $ace, $field )
    {
        $objectFieldACEs = $acl->getObjectFieldACEs( $field );
        $countACEs      = count( $objectFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $objectFieldACEs[ $i ]->getId() )
            {
                $acl->deleteObjectFieldACE( $i, $field );

                break;
            }
        }

        return $acl;
    }
}
