<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Fixture\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use ENC\Bundle\ApplicationServiceAbstractBundle\Entity\HasCollectionInterface;

class HasCollectionEntity implements HasCollectionInterface
{
    public function createCollection()
    {
        return new ArrayCollection();
    }
    
    public function setCollection(ArrayCollection $collection)
    {
    }
    
    public function setEntity($entity)
    {
    }
}