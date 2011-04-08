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
        return new ObjectIdentity(-1, $class);
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
    
    public function findAcl( ObjectIdentity $oid, array $sid )
    {
        return $this->getAclProvider()->findAcl( $oid, $sid );
    }

    public function getACEForMaskAndUserIdentity( $ACEsList, $mask, SecurityIdentityInterface $userIdentity )
    {
        foreach ( $ACEsList as $ace )
        {
            if ( $ace->getMask() === $mask->get() && $ace->getSecurityIdentity()->equals( $userIdentity ) )
            {
                return $ace;
            }
        }

        return false;
    }

    public function getFieldACEForMaskAndUserIdentity( $ACEsList, $mask, SecurityIdentityInterface $userIdentity, $field )
    {
        foreach ( $ACEsList as $ace )
        {
            if ( $ace->getField() === $field && $ace->getMask() === $mask->get() && $ace->getSecurityIdentity()->equals( $userIdentity ) )
            {
                return $ace;
            }
        }

        return false;
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

    public function updateClassACE( $acl, $ace, $mask )
    {
        $classACEs = $acl->getClassACEs();
        $countACEs = count( $classACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $classACEs[ $i ]->getId() )
            {
                $acl->updateClassACE( $i, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateClassFieldACE( $acl, $ace, $field, $mask )
    {
        $classFieldACEs = $acl->getClassFieldACEs();
        $countACEs = count( $classFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $classFieldACEs[ $i ]->getId() )
            {
                $acl->updateClassFieldACE( $i, $field, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateObjectACE( $acl, $ace, $mask )
    {
        $objectACEs = $acl->getObjectACEs();
        $countACEs = count( $objectACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $objectACEs[ $i ]->getId() )
            {
                $acl->updateObjectACE( $i, $mask );

                break;
            }
        }

        return $acl;
    }

    public function updateObjectFieldACE( $acl, $ace, $field, $mask )
    {
        $objectFieldACEs = $acl->getObjectFieldACEs($field);
        $countACEs = count( $objectFieldACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $objectFieldACEs[ $i ]->getId() )
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
