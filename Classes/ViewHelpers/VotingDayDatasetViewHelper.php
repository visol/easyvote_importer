<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Lorenz Ulrich <lorenz.ulrich@visol.ch>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Lorenz Ulrich <lorenz.ulrich@visol.ch>
 * @package EasyvoteImporter
 */
class Tx_EasyvoteImporter_ViewHelpers_VotingDayDatasetViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initializeArguments() {
		//$this->registerArgument('returnDataset', 'string', 'Template variable name to assign. If not specified returns the result array instead');
	}

	/**
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $datasets
	 * @param string $returnDataset
	 * @return string
	 */
	public function render(\Visol\Easyvote\Domain\Model\VotingDay $votingDay, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $datasets, $returnDataset) {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();

		$output = '';
		if (count($arguments['datasets'])) {
			foreach ($arguments['datasets'] as $dataset) {
				$votingDayTimestamp = $arguments['votingDay']->getVotingDate()->getTimestamp();
				$datasetTimestamp = $dataset->getVotingDay()->getVotingDate()->getTimestamp();
				if ($votingDayTimestamp === $datasetTimestamp) {
					$templateVariableContainer->add($arguments['returnDataset'], $dataset);
					$output = $renderChildrenClosure();
					$templateVariableContainer->remove($arguments['returnDataset']);
					break;
				} else {
					$templateVariableContainer->add($arguments['returnDataset'], '');
					$output = $renderChildrenClosure();
					$templateVariableContainer->remove($arguments['returnDataset']);
				}
			}
		} else {
			$templateVariableContainer->add($arguments['returnDataset'], '');
			$output = $renderChildrenClosure();
			$templateVariableContainer->remove($arguments['returnDataset']);
		}
		return $output;
	}

}