<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseCrudController extends Controller
{
	public function findAction()
    {
		$moduleManager 	= $this->container->get( 'module_manager' );
		$module			= $moduleManager->getModule( $this->getMainModuleID() );
		$service		= $module->getService( $this->getMainModuleServiceID() );
		$request		= $service->getServiceRequest();
		$response		= $request->getDataFromIndex( 'returnOne' ) === null ? $service->findFromRequest() : $service->findOneFromRequest();
		
		return new Response( $response->getResponse() );
    }
	
	public function createAction()
    {
        $moduleManager 	= $this->container->get( 'module_manager' );
		$module			= $moduleManager->getModule( $this->getMainModuleID() );
		$service		= $module->getService( $this->getMainModuleServiceID() );
		$response		= $service->createFromRequest();
		
		return new Response( $response->getResponse() );
    }
	
	public function updateAction()
    {
        $moduleManager 	= $this->container->get( 'module_manager' );
		$module			= $moduleManager->getModule( $this->getMainModuleID() );
		$service		= $module->getService( $this->getMainModuleServiceID() );
		$response		= $service->updateFromRequest();
		
		return new Response( $response->getResponse() );
    }
	
	public function deleteAction()
    {
        $moduleManager 	= $this->container->get( 'module_manager' );
		$module			= $moduleManager->getModule( $this->getMainModuleID() );
		$service		= $module->getService( $this->getMainModuleServiceID() );
		$response		= $service->deleteFromRequest();
		
		return new Response( $response->getResponse() );
    }
    
    public function getMainModuleService()
    {
        $moduleManager 	= $this->container->get( 'module_manager' );
		$module			= $moduleManager->getModule( $this->getMainModuleID() );
        
        return $module->getService( $this->getMainModuleServiceID() );
    }
	
	public function getMainModuleServiceID()
	{
		return $this->getMainModuleID().'_service';
	}
}
