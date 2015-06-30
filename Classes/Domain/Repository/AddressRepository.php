<?php
namespace Visol\EasyvoteImporter\Domain\Repository;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
				$query->like('name', '%' . trim($blacklistItem->getFirstName()) . '%'),
				$query->like('name', '%' . trim($blacklistItem->getLastName()) . '%'),
				$query->like('street', trim($blacklistItem->getStreet())),
				$query->like('city', '%' . trim($blacklistItem->getZipCode()) . '%')
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

	/**
	 * Removes addresses that belong to a given dataset
	 * Returns the number of deleted addresses
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @return integer
	 */
	public function removeByDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$whereClause = 'dataset=' . $dataset->getUid();
		$databaseConnection->exec_DELETEquery('tx_easyvoteimporter_domain_model_address', $whereClause);
		return $databaseConnection->sql_affected_rows();

	}

}
