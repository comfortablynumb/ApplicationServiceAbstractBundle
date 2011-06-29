<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

use ENC\Bundle\ApplicationServiceAbstractBundle\ApplicationService\ApplicationService;

class ApplicationServiceAbstractExtension extends Extension
{   
    protected $concurrencyLockTypes = array(
        'none'              => ApplicationService::CONCURRENCY_LOCK_NONE,
        'optimistic'        => ApplicationService::CONCURRENCY_LOCK_OPTIMISTIC,
        'pessimistic_read'  => ApplicationService::CONCURRENCY_LOCK_PESSIMISTIC_READ,
        'pessimistic_write' => ApplicationService::CONCURRENCY_LOCK_PESSIMISTIC_WRITE
    );
    
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('config.xml');
        
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        
        if (isset($config['concurrency_lock_type'])) {
            $this->registerConcurrencyLockTypeConfiguration($config['concurrency_lock_type'], $container);
        }
        
        if (isset($config['base_class'])) {
            $container->setParameter('application_service_abstract.service.class', $config['base_class']);
        }
        
        if (isset($config['request'])) {
            $this->registerRequestConfiguration($config['request'], $container);
        }
        
        if (isset($config['response'])) {
            $this->registerResponseConfiguration($config['response'], $container);
        }
        
        if (isset($config['acl_manager'])) {
            $this->registerAclManagerConfiguration($config['acl_manager'], $container);
        }
        
        if (isset($config['validation_errors_formatter'])) {
            $this->registerValidationErrorsFormatterConfiguration($config['validation_errors_formatter'], $container);
        }
        
        if (isset($config['log_formatter'])) {
            $this->registerValidationErrorsFormatterConfiguration($config['log_formatter'], $container);
        }
    }
    
    public function registerConcurrencyLockTypeConfiguration($concurrencyLockType, ContainerBuilder $container)
    {
        $concurrencyLockType = $this->concurrencyLockTypes[$concurrencyLockType];
        
        $definition = $container->getDefinition('application_service_abstract.service');
        $definition->addMethodCall('setConcurrencyLockType', array($concurrencyLockType));
    }
    
    public function registerRequestConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('application_service_abstract.request.class', $config['class']);
        }
    }
    
    public function registerResponseConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('application_service_abstract.response.class', $config['class']);
        }
    }
    
    public function registerAclManagerConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('application_service_abstract.acl_manager.class', $config['class']);
        }
    }
    
    public function registerValidationErrorsFormatterConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('application_service_abstract.validation_errors_formatter.class', $config['class']);
        }
    }
    
    public function registerLogFormatterConfiguration(array $config, ContainerBuilder $container)
    {
        if (isset($config['class'])) {
            $container->setParameter('application_service_abstract.log_formatter.class', $config['class']);
        }
    }
    
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/';
    }
}
