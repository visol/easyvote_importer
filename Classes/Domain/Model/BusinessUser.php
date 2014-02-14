<?php
namespace Visol\EasyvoteImporter\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Lorenz Ulrich <lorenz.ulrich@visol.ch>, visol digitale Dienstleistungen GmbH
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class BusinessUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser {

	/**
	 * Kundennummer
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $customerNumber;

	/**
	 * Korrespondenzsprache
	 *
	 * @var \integer
	 */
	protected $userLanguage;

	/**
	 * Zielgruppe Start
	 *
	 * @var \string
	 */
	protected $targetGroupStart;

	/**
	 * Zielgruppe Ende
	 *
	 * @var \string
	 */
	protected $targetGroupEnd;

	/**
	 * Adressdaten
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteImporter\Domain\Model\Dataset>
	 * @lazy
	 */
	protected $datasets;

	/**
	 * __construct
	 *
	 * @return BusinessUser
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->datasets = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Returns the customerNumber
	 *
	 * @return \string $customerNumber
	 */
	public function getCustomerNumber() {
		return $this->customerNumber;
	}

	/**
	 * Sets the customerNumber
	 *
	 * @param \string $customerNumber
	 * @return void
	 */
	public function setCustomerNumber($customerNumber) {
		$this->customerNumber = $customerNumber;
	}

	/**
	 * Adds a Dataset
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @return void
	 */
	public function addDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {
		$this->datasets->attach($dataset);
	}

	/**
	 * Removes a Dataset
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $datasetToRemove The Dataset to be removed
	 * @return void
	 */
	public function removeDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $datasetToRemove) {
		$this->datasets->detach($datasetToRemove);
	}

	/**
	 * Returns the datasets
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteImporter\Domain\Model\Dataset> $datasets
	 */
	public function getDatasets() {
		return $this->datasets;
	}

	/**
	 * Sets the datasets
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteImporter\Domain\Model\Dataset> $datasets
	 * @return void
	 */
	public function setDatasets(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $datasets) {
		$this->datasets = $datasets;
	}

	/**
	 * @return mixed
	 */
	public function getUserLanguage() {
		return $this->userLanguage;
	}

	/**
	 * @param mixed $userLanguage
	 */
	public function setUserLanguage($userLanguage) {
		$this->userLanguage = $userLanguage;
	}

	/**
	 * @return string
	 */
	public function getTargetGroupStart() {
		return $this->targetGroupStart;
	}

	/**
	 * @param string $targetGroupStart
	 */
	public function setTargetGroupStart($targetGroupStart) {
		$this->targetGroupStart = $targetGroupStart;
	}

	/**
	 * @return string
	 */
	public function getTargetGroupEnd() {
		return $this->targetGroupEnd;
	}

	/**
	 * @param string $targetGroupEnd
	 */
	public function setTargetGroupEnd($targetGroupEnd) {
		$this->targetGroupEnd = $targetGroupEnd;
	}

}
?>