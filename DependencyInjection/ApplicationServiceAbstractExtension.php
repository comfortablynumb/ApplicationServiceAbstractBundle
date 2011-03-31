<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;

class ApplicationServiceAbstractExtension extends Extension
{
    public function load( array $config, ContainerBuilder $container )
    {
		$loader = new XmlFileLoader( $container, new FileLocator(__DIR__ . '/../Resources/config' ) );
		$loader->load( 'config.xml' );
    }
	
	public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/';
    }

    public function getAlias()
    {
        return 'application_service_abstract';
    }
}
