<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection\Compiler\ApplicationServicePass;

class ApplicationServiceAbstractBundle extends Bundle
{
    public function getNamespace()
    {
        return __NAMESPACE__;
    }
    
    public function getPath()
    {
        return strtr( __DIR__, '\\', '/' );
    }
    
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        
        $container->addCompilerPass( new ApplicationServicePass() );
    }
}
