<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory;

use ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Factory\TestBaseEntityFactory;

class TestHasCollectionEntityFactory extends TestBaseEntityFactory
{
    public function getClassName()
    {
        return 'ENC\Bundle\ApplicationServiceAbstractBundle\Tests\Fixture\Entity\HasCollectionEntity';
    }
}