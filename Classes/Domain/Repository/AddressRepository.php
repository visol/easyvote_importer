<?php
namespace Visol\EasyvoteImporter\Domain\Repository;

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
use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AddressRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Find an address that matches a given blacklist entry by voting day
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklistItem
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return object
	 */
	public function findBlacklistMatchByVotingDay(\Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklistItem, \Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->like('votingDay', $votingDay),
				$query->like('name', '%' . $blacklistItem->getFirstName() . '%'),
				$query->like('name', '%' . $blacklistItem->getLastName() . '%'),
				$query->like('street', $blacklistItem->getStreet()),
				$query->like('city', '%' . $blacklistItem->getZipCode() . '%')
			)
		);
		return $query->execute()->getFirst();
	}

	/**
	 * Find all addresses for a voting day that are *not* blacklisted
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAllNotBlacklistedByVotingDay(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->like('votingDay', $votingDay),
				$query->equals('blacklisted', FALSE)
			)
		);
		return $query->execute();
	}

	/**
	 * Find all addresses for a voting day that are *not* blacklisted
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return array
	 */
	public function findAllNotBlacklistedByVotingDayArray(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$whereClause = 'voting_day=' . $votingDay->getUid() . ' AND blacklisted=0';
		return $databaseConnection->exec_SELECTgetRows('*', 'tx_easyvoteimporter_domain_model_address', $whereClause);
	}

	/**
	 * Find all addresses for a voting day that are blacklisted
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findByVotingDayAndBlacklist(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('votingDay', $votingDay),
				$query->equals('blacklisted', TRUE)
			)
		);
		return $query->execute();
	}

	/**
	 * Find all not blacklisted addresses by businessUser and votingDay
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findByDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('dataset', $dataset),
				$query->equals('blacklisted', FALSE)
			)
		);
		return $query->execute();
	}

}
?>