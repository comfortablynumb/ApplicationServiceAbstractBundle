<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\AclManager;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class AclManager
{
    protected $aclProvider = null;
    
    public function __construct( $container )
    {
        $this->setAclProvider( $container->get( 'security.acl.provider' ) );
    }
    
    public function getAclProvider()
    {
        return $this->aclProvider;
    }
    
    public function setAclProvider( $aclProvider )
    {
        $this->aclProvider = $aclProvider;
    }
    
    public function createObjectIdentity( $entity )
    {
        return ObjectIdentity::fromDomainObject( $entity );
    }
    
    public function createObjectIdentityForClass( $class )
    {
        return new ObjectIdentity( -1, $class );
    }
    
    public function createUserSecurityIdentity( $user )
    {
        return UserSecurityIdentity::fromAccount( $user );
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

    public function getACEForMask( $ACEsList, $mask )
    {
        foreach ( $ACEsList as $ace )
        {
            if ( $ace->getMask() === $mask->get() )
            {
                return $ace;
            }
        }

        return false;
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

    public function updateClassACE( $acl, $ace )
    {
        $classACEs = $acl->getClassACEs();
        $countACEs = count( $classACEs );

        for ( $i = 0 ; $i < $countACEs ; ++$i )
        {
            if ( $ace->getId() === $classACEs[ $i ]->getId() )
            {
                $acl->updateClassACE( $i );

                break;
            }
        }

        return $acl;
    }
}
