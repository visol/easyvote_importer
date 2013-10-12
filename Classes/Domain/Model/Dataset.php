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
class Dataset extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Datei
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $file;

	/**
	 * Spaltennamen im ersten Datensatz?
	 *
	 * @var boolean
	 */
	protected $firstrowColumnnames = FALSE;

	/**
	 * Spaltenzuweisung
	 *
	 * @var \string
	 */
	protected $columnConfiguration;

	/**
	 * Upload-Datum
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $crdate;

	/**
	 * Abstimmungstag
	 *
	 * @var \Visol\Easyvote\Domain\Model\VotingDay
	 * @lazy
	 */
	protected $votingDay;

	/**
	 * Benutzer
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser
	 * @lazy
	 */
	protected $businessuser;

	/**
	 * Returns the file
	 *
	 * @return \string $file
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets the file
	 *
	 * @param \string $file
	 * @return void
	 */
	public function setFile($file) {
		$this->file = $file;
	}

	/**
	 * Returns the firstrowColumnnames
	 *
	 * @return boolean $firstrowColumnnames
	 */
	public function getFirstrowColumnnames() {
		return $this->firstrowColumnnames;
	}

	/**
	 * Sets the firstrowColumnnames
	 *
	 * @param boolean $firstrowColumnnames
	 * @return void
	 */
	public function setFirstrowColumnnames($firstrowColumnnames) {
		$this->firstrowColumnnames = $firstrowColumnnames;
	}

	/**
	 * Returns the columnConfiguration
	 *
	 * @return \string $columnConfiguration
	 */
	public function getColumnConfiguration() {
		return $this->columnConfiguration;
	}

	/**
	 * Sets the columnConfiguration
	 *
	 * @param \string $columnConfiguration
	 * @return void
	 */
	public function setColumnConfiguration($columnConfiguration) {
		$this->columnConfiguration = $columnConfiguration;
	}

	/**
	 * Returns the crdate
	 *
	 * @return \string $crdate
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	/**
	 * Sets the crdate
	 *
	 * @param \string $crdate
	 * @return void
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * Returns the votingDay
	 *
	 * @return \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 */
	public function getVotingDay() {
		return $this->votingDay;
	}

	/**
	 * Sets the votingDay
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return void
	 */
	public function setVotingDay(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$this->votingDay = $votingDay;
	}

	/**
	 * Returns the businessuser
	 *
	 * @return \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessuser
	 */
	public function getBusinessuser() {
		return $this->businessuser;
	}

	/**
	 * Sets the businessuser
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessuser
	 * @return void
	 */
	public function setBusinessuser(\Visol\Easyvote\Domain\Model\BusinessUser $businessuser) {
		$this->businessuser = $businessuser;
	}

}
?>