<?php
 
namespace F3\Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Admin".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Model controller for the Admin package
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ModelController extends \F3\FLOW3\MVC\Controller\ActionController {
	/**
	 * @var \F3\Admin\Utilities
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $utilities;

	/**
	 *
	 * @param \F3\Admin\Utilities $utilities
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectUtilities(\F3\Admin\Utilities $utilities) {
		$this->utilities = $utilities;
	}

	/**
	 * @var \F3\FLOW3\Property\Mapper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $mapper;
	
	/**
	 * Injects the property mapper
	 *
	 * @param \F3\FLOW3\Property\Mapper $mapper The property mapper
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectMapper(\F3\FLOW3\Property\Mapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * @var \F3\FLOW3\Object\ManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $objectManager;

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ManagerInterface $manager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectManager(\F3\FLOW3\Object\ManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	 * @var \F3\FLOW3\Reflection\Service
	 * @author Marc Neuhaus
	 */
	protected $reflectionService;

	/**
	 * Injects the reflection service
	 *
	 * @param \F3\FLOW3\Reflection\Service $reflectionService
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @var \F3\FLOW3\Configuration\Manager
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $configurationManager;

	/**
	 * @var \F3\FLOW3\Package\ManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $packageManager;

	/**
	 * Injects the packageManager
	 *
	 * @param \F3\FLOW3\Package\ManagerInterface $packageManager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function inject(\F3\FLOW3\Package\ManagerInterface $packageManager) {
		$this->packageManager = $packageManager;
	}

	/**
	 * @var \F3\FLOW3\Persistence\ManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param \F3\FLOW3\Persistence\ManagerInterface $persistenceManager
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function injectPersistenceManager(\F3\FLOW3\Persistence\ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Index action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function indexAction() {
		if($this->request->hasArgument("package")){
			$package = $this->request->getArgument("package");
			$allPackages = $this->utilities->getEnabledModels();
			$packages = array(
				$package => $allPackages[$package]
			);
			$this->view->assign('packages', $packages);
		}else{			
			$packages = $this->utilities->getEnabledModels();
			$this->view->assign('packages', $packages);
		}

		$current = $this->request->hasArgument("package") ? $this->request->getArgument("package") : "Overview";
		$this->view->assign('current', $current);
		$overview = $this->request->hasArgument("package") ? false : true;
		$this->view->assign('overview', $overview);
	}

	/**
	 * View action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function listAction() {
		$model = $this->request->getArgument("model");
		$this->view->assign("model",$model);

		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$repositoryObject = $this->objectManager->getObject($repository);

		$objects = $repositoryObject->findAll();
		$this->view->assign("objects",$objects);

		$properties = $this->getModelProperties($model);
		$this->view->assign("properties",$properties);

		$propertyCount = count($properties) + 1;
		$this->view->assign("propertyCount",$propertyCount);

		$bulkActions = array(
			"none"=>' ',
			"F3\Admin\BulkActions\DeleteBulkAction"=>'Delete selected Items'
		);
		$this->view->assign("bulkActions",$bulkActions);
	}

	/**
	 * update action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function updateAction() {
		$tmp = $this->request->getArgument("item");
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->getModelProperties($modelClass);

		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->persistenceManager->getBackend()->getObjectByIdentifier($tmp["__identity"]);

		if($this->request->hasArgument("update")){
			$errors = $this->createUpdateObject("create",$object);
			if($errors === false){
				$arguments = array("model"=>$this->request->getArgument("model"));
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				$this->view->assign("errors",$errors);
			}
		}
		
		if($this->request->hasArgument("delete")){
			$repository = str_replace("Domain\Model","Domain\Repository",$modelClass) . "Repository";
			$repositoryObject = $this->objectManager->getObject($repository);
			$repositoryObject->remove($object);
			$this->persistenceManager->persistAll();
			
			$arguments = array("model"=>$this->request->getArgument("model"));
			$this->redirect('list',NULL,NULL,$arguments);
		}
		
		$this->view->assign('object',$object);
	}

	/**
	 * bulk action
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function bulkAction() {
		$tmp = $this->request->getArgument("bulk");
		$identifiers = array();
		foreach(array_keys($tmp["__identity"]) as $identifier){
			$identifiers[] = $identifier;
		}

		$action = $this->request->getArgument("action");
		$actionObject = $this->objectManager->getObject($action);
		$actionObject->action($identifiers);

		$this->redirect('list',NULL,NULL,array("model"=>$this->request->getArgument("model")));
	}

	/**
	 * Creates a new blog
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 */
	public function createAction() {
		$modelClass = $this->request->getArgument("model");
		$model = $this->objectFactory->create($modelClass);

		$properties = $this->getModelProperties($modelClass);

		$this->view->assign('model', $modelClass);
		$this->view->assign('properties',$properties);

		$object = $this->objectFactory->create($modelClass);

		if($this->request->hasArgument("item")){
			$errors = $this->createUpdateObject("create",$object);
			if($errors === false){
				$arguments = array("model"=>$this->request->getArgument("model"));
				$this->redirect('list',NULL,NULL,$arguments);
			}else{
				$this->view->assign("errors",$errors);
			}
		}
		
		$this->view->assign('object',$object);
	}
	
	/**
	 * Checks if the Widget provides a Function to convert the incoming Form 
	 * Data into ContentRepository Data.
	 * 
	 * Note: This might become obsolete because of the enhancement of the Propertymapper
	 *
	 * @return void
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function convertArray($array,$class){
		$properties = $this->reflectionService->getClassPropertyNames($class);
		foreach ($properties as $property) {
			$tags = $this->reflectionService->getPropertyTagsValues($class,$property);
			if(in_array($property,array_keys($array))){
				$widgetClass = $this->utilities->getWidgetClass($tags["var"][0]);
                $widget = $this->objectFactory->create($widgetClass);
				if(method_exists($widget,"convert"))
					$array[$property] = $widget->convert($array[$property]);
			}
		}
		return $array;
	}
	
	/**
	 * Returns all Properties of a Specified Models
	 *
	 * œparam $model String Name of the Model
	 * @return $properties Array of Model Properties
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function getModelProperties($model){
		$tmpProperties = $this->reflectionService->getClassPropertyNames($model);
		foreach ($tmpProperties as $property) {
			$properties[$property] = $this->reflectionService->getPropertyTagsValues($model,$property);
			if(!in_array("var",array_keys($properties[$property]))) continue;
			$properties[$property]["identity"] = in_array("identity",array_keys($properties[$property])) ? "true" : "false";
		}
		unset($tmpProperties);
		return $properties;
	}

	/**
	 * This Method handles the Creation or Update of the Posted Model
	 * 
	 * TODO: The Validation isn't working
	 *
	 * @param $mode String Mode Create/Update
	 * @param $targetObject Object
	 * @return $success Boolean
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
	public function createUpdateObject($mode,&$targetObject){
		$model = $this->request->getArgument("model");
		$modelName = $this->utilities->getObjectNameByClassName($model);
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		$modelValidator = $this->utilities->getModelValidator($model);
		
		$item = $this->convertArray($this->request->getArgument("item"),$model);
		$this->propertyMapper->mapAndValidate(array_keys($item), $item, $targetObject,array(),$modelValidator);
		
		$repositoryObject = $this->objectManager->getObject($repository);
		
		$errors = $modelValidator->getErrors();
		
		if(count($errors)>0){
			return $errors;
		}else{
			if($mode=="create")
				$repositoryObject->add($targetObject);
			if($mode=="update")
				$repositoryObject->update($targetObject);
			return false;
		}
	}
}

?>