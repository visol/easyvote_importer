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
class Address extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Person wird vom Export ausgeschlossen
	 *
	 * @var boolean
	 */
	protected $blacklisted = FALSE;

	/**
	 * Kundennummer
	 *
	 * @var \string
	 */
	protected $customerNumber;

	/**
	 * Anrede
	 *
	 * @var \string
	 */
	protected $salutation;

	/**
	 * Vorname und Name
	 *
	 * @var \string
	 */
	protected $name;

	/**
	 * Adresse
	 *
	 * @var \string
	 */
	protected $street;

	/**
	 * PLZ und Ort
	 *
	 * @var \string
	 */
	protected $city;

	/**
	 * Import-Datum
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
	 * Name der Datei
	 *
	 * @var \string
	 */
	protected $importFileName;

	/**
	 * Returns the importFileName
	 *
	 * @return \string $importFileName
	 */
	public function getImportFileName() {
		return $this->importFileName;
	}

	/**
	 * Sets the importFileName
	 *
	 * @param \string $importFileName
	 * @return void
	 */
	public function setImportFileName($importFileName) {
		$this->importFileName = $importFileName;
	}

	/**
	 * Returns the blacklisted state
	 *
	 * @return boolean $blacklisted
	 */
	public function getBlackListed() {
		return $this->blacklisted;
	}

	/**
	 * Sets the blacklisted state
	 *
	 * @param boolean $blacklisted
	 * @return void
	 */
	public function setBlacklisted($blacklisted) {
		$this->blacklisted = $blacklisted;
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
	 * Returns the salutation
	 *
	 * @return \string $salutation
	 */
	public function getSalutation() {
		return $this->salutation;
	}

	/**
	 * Sets the salutation
	 *
	 * @param \string $salutation
	 * @return void
	 */
	public function setSalutation($salutation) {
		$this->salutation = $salutation;
	}

	/**
	 * Returns the name
	 *
	 * @return \string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param \string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the street
	 *
	 * @return \string $street
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * Sets the street
	 *
	 * @param \string $street
	 * @return void
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * Returns the city
	 *
	 * @return \string $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets the city
	 *
	 * @param \string $city
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Returns the lowerCamelCase
	 *
	 * @return \string $lowerCamelCase
	 */
	public function getUpperCamelCase() {
		return $this->lowerCamelCase;
	}

	/**
	 * Sets the lowerCamelCase
	 *
	 * @param \string $lowerCamelCase
	 * @return void
	 */
	public function setUpperCamelCase($lowerCamelCase) {
		$this->lowerCamelCase = $lowerCamelCase;
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
	 * @return integer $votingDay
	 */
	public function getVotingDay() {
		return $this->votingDay;
	}

	/**
	 * Sets the votingDay
	 *
	 * @param integer $votingDay
	 * @return void
	 */
	public function setVotingDay($votingDay) {
		$this->votingDay = $votingDay;
	}

}
?>