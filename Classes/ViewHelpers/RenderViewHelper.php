<?php

namespace Foo\ContentManagement\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * TODO: (SK) get rid of this view helper. Duplicates lots of FLOW3 code.
 * 		 (MN) Agreed in general, only difference here is the usage of those
 *    		  Fallbacks for Templates again. IMHO this is one feature we 
 *        	  should try to keep, because it makes overriting Template 
 *            for certain things a breeze
 *
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class RenderViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \Foo\ContentManagement\Adapters\ContentManager
	 * @FLOW3\Inject
	 */
	protected $contentManager;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Fluid\Core\Parser\TemplateParser
	 * @FLOW3\Inject
	 */
	protected $templateParser;

	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;

	/**
	 *
	 * @param object $value
	 * @param string $partial
	 * @param string $fallbacks
	 * @param array $vars
	 * @param string $section
	 * @param mixed $optional
	 * @param string $variant
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($value='',$partial='',$fallbacks='',$vars = array(), $section = null, $optional = false, $variant = "Default") {
		if($value !== '')
			return $value;

		if ($partial !== '' && !is_null($partial)) {
			if($fallbacks !== ''){
				$replacements = array(
					"@partial" => $partial,
					"@package" => \Foo\ContentManagement\Core\API::get("package"),
					"@being" => $this->contentManager->getShortName(\Foo\ContentManagement\Core\API::get("being")),
					"@action" => $partial,
					"@variant" => $variant
				);

				$cache = $this->cacheManager->getCache('Admin_TemplateCache');
				$identifier = str_replace("\\","_",implode("-",$replacements));
				$identifier = str_replace(".","_",$identifier);
				$identifier = str_replace("/","_",$identifier);
				$identifier = str_replace(" ","_",$identifier);
				if(!$cache->has($identifier)){
					$template = $this->getPathByPatternFallbacks($fallbacks,$replacements);
					$cache->set($identifier,$template);
				}else{
					$template = $cache->get($identifier);
				}

				if(empty($vars) && false){
					$this->view = $this->viewHelperVariableContainer->getView();
					$this->view->setTemplatePathAndFilename($template);

					if(!empty($template)){
						return $this->view->render($partial);
					}
				}else{
					$partial = $this->parseTemplate($template);
					$variableContainer = $this->objectManager->get('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', $vars);
					$renderingContext = $this->buildRenderingContext($variableContainer);
					return $partial->render($renderingContext);
				}
			}
		}

		if($section !== null){
 			$output = $this->viewHelperVariableContainer->getView()->renderSection($section, $vars, $optional);
			if(strlen($output) < 1)
				$output = $this->renderChildren();
			return $output;
		}
	}

   protected function parseTemplate($templatePathAndFilename) {
		$templateSource = \TYPO3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
		if ($templateSource === FALSE) {
			throw new \TYPO3\Fluid\View\Exception\InvalidTemplateResourceException('"' . $templatePathAndFilename . '" is not a valid template resource URI.', 1257246929);
		}
		return $this->templateParser->parse($templateSource);
	}

	/**
	 * Build the rendering context
	 *
	 * @param \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer
	 * @return \TYPO3\Fluid\Core\Rendering\RenderingContext
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildRenderingContext(\TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer = NULL) {
		if ($variableContainer === NULL) {
			$variableContainer = $this->objectManager->get('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->variables);
		}

		$renderingContext = $this->objectManager->get('TYPO3\Fluid\Core\Rendering\RenderingContext');
		$renderingContext->injectTemplateVariableContainer($variableContainer);
		if ($this->controllerContext !== NULL) {
			$renderingContext->setControllerContext($this->controllerContext);
		}

		$viewHelperVariableContainer = $this->objectManager->get('TYPO3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
		$viewHelperVariableContainer->setView($this->viewHelperVariableContainer->getView());
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);

		return $renderingContext;
	}

	/**
	 * returns a template Path by checking configured fallbacks
	 *
	 * @param string $patterns
	 * @param string $replacements
	 * @return $path String
	 * @author Marc Neuhaus
	 */
	public function getPathByPatternFallbacks($patterns, $replacements){
		if(is_string($patterns)){
			$paths = explode(".",$patterns);
			$patterns = $this->contentManager->getSettings();
			$patterns = $patterns["Fallbacks"];
			foreach ($paths as $path) {
				$patterns = $patterns[$path];
			}
		}

		foreach($patterns as $pattern){
			$pattern = str_replace(array_keys($replacements),array_values($replacements),$pattern);
			$tried[] = $pattern;
			if(file_exists($pattern)){
				return $pattern;
			}
		}

		throw new \Exception('Could not find any Matching Path. Tried: '.implode(", ", $tried).'');
	}
}

?>
