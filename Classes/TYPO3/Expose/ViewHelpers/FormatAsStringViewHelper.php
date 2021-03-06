<?php
namespace TYPO3\Expose\ViewHelpers;

use TYPO3\Expose\Utility\StringRepresentation;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Expose".          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class FormatAsStringViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @return string Rendered string
	 */
	public function render() {
		// StringRepresentation::setTypoScriptRuntime($this->viewHelperVariableContainer->getView()->getTypoScriptObject());
		return StringRepresentation::convert($this->renderChildren());
	}
}

?>