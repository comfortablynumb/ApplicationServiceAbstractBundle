<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{
    public function getKernel()
    {
        if (is_null(self::$kernel)) {
            self::$kernel = $this->createKernel();
            self::$kernel->boot();
        }
        
        return self::$kernel;
    }
    
    public function getContainer()
    {
        return $this->getKernel()->getContainer();
    }
}