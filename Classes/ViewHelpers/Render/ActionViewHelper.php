<?php
namespace Foo\ContentManagement\ViewHelpers\Render;

/*                                                                        *
 * This script belongs to the Foo.ContentManagement package.              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Render a Action for an Object or class
 *
 * The factory class must implement {@link TYPO3\Form\Factory\FormFactoryInterface}.
 *
 * @api
 */
class ActionViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @param string $action name of the entry action
	 * @param string $controller name of the entry controller
	 * @param string $class the class to render the form for
	 * @param object $object the object to rende the form for
	 * @param array $overrideConfiguration factory specific configuration
	 * @return string the rendered form
	 */
	public function render($action = "index" , $controller = "Foo\ContentManagement\Controller\IndexController", $class = NULL, $object = NULL, array $overrideConfiguration = array()) {
		$response = new \TYPO3\FLOW3\Http\Response($this->controllerContext->getResponse());
		$request = $this->controllerContext->getRequest();
		$actionRuntime = new \Foo\ContentManagement\Core\ActionRuntime($request, $response);

		if(!is_null($class))
			$actionRuntime->setDefaultBeing($class);

		$actionRuntime->setDefaultAction($action);
		$actionRuntime->setDefaultController($controller);

		return $actionRuntime->execute();
	}
}
?>