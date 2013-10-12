<?php

namespace Visol\EasyvoteImporter\Tests;
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
 * Test case for class \Visol\EasyvoteImporter\Domain\Model\BusinessUser.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Lorenz Ulrich <lorenz.ulrich@visol.ch>
 */
class BusinessUserTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \Visol\EasyvoteImporter\Domain\Model\BusinessUser();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getCustomerNumberReturnsInitialValueForString() {
		$this->assertSame(
			NULL,
			$this->fixture->getCustomerNumber()
		);
	}

	/**
	 * @test
	 */
	public function setCustomerNumberForStringSetsCustomerNumber() {
		$this->fixture->setCustomerNumber('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getCustomerNumber()
		);
	}
	/**
	 * @test
	 */
	public function getDatasetsReturnsInitialValueForDataset() {
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getDatasets()
		);
	}

	/**
	 * @test
	 */
	public function setDatasetsForObjectStorageContainingDatasetSetsDatasets() {
		$dataset = new \Visol\EasyvoteImporter\Domain\Model\Dataset();
		$objectStorageHoldingExactlyOneDatasets = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneDatasets->attach($dataset);
		$this->fixture->setDatasets($objectStorageHoldingExactlyOneDatasets);

		$this->assertSame(
			$objectStorageHoldingExactlyOneDatasets,
			$this->fixture->getDatasets()
		);
	}

	/**
	 * @test
	 */
	public function addDatasetToObjectStorageHoldingDatasets() {
		$dataset = new \Visol\EasyvoteImporter\Domain\Model\Dataset();
		$objectStorageHoldingExactlyOneDataset = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneDataset->attach($dataset);
		$this->fixture->addDataset($dataset);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneDataset,
			$this->fixture->getDatasets()
		);
	}

	/**
	 * @test
	 */
	public function removeDatasetFromObjectStorageHoldingDatasets() {
		$dataset = new \Visol\EasyvoteImporter\Domain\Model\Dataset();
		$localObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$localObjectStorage->attach($dataset);
		$localObjectStorage->detach($dataset);
		$this->fixture->addDataset($dataset);
		$this->fixture->removeDataset($dataset);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getDatasets()
		);
	}
}
?>