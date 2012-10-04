<?php
namespace TYPO3\Expose\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Expose".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Action to Update the Being
 *
 */
class EditController extends AbstractController {

	/**
	 * @return void
	 */
	public function initializeIndexAction() {
		$this->arguments['objects']->setDataType('Doctrine\Common\Collections\Collection<' . $this->request->getArgument('type') . '>');
		$this->arguments['objects']->getPropertyMappingConfiguration()->allowAllProperties();
	}

	/**
	 * Edit object
	 *
	 * @param string $type
	 * @param \Doctrine\Common\Collections\Collection $objects
	 * @return void
	 */
	public function indexAction($type, $objects) {
		$this->view->assign('className', $type);
		$this->view->assign('objects', $objects);
		$this->view->assign('callback', 'update');
	}

	/**
	 * @return void
	 */
	public function initializeUpdateAction() {
		$this->arguments['objects']->setDataType('Doctrine\Common\Collections\Collection<' . $this->request->getArgument('type') . '>');
		$propertyMappingConfiguration = $this->arguments['objects']->getPropertyMappingConfiguration();
		$propertyMappingConfiguration->allowAllProperties();
		foreach ($this->request->getArgument('objects') as $index => $tmp) {
			$propertyMappingConfiguration
				->forProperty($index)
				->allowAllProperties()
				->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
		}

	}
	/**
	 * @param string $type
	 * @param \Doctrine\Common\Collections\Collection $objects
	 * @return void
	 */
	public function updateAction($type, $objects) {
		foreach ($objects as $object) {
				// TODO: the if-condition below is a little hack such that we do NOT persist for TYPO3CR Node objects,
				// which are already persisted as they are stateful.
			if (!$this->persistenceManager->isNewObject($object)) {
				$this->persistenceManager->update($object);
			}
		}
		$this->redirect('index', 'sametypelist', 'TYPO3.Expose', array('type' => $type));
	}
}
?>