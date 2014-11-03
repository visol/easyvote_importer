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

/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DatasetRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return object
	 */
	public function findDatasetByBusinessUserAndVotingDate(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser, \Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('businessuser', $businessUser),
				$query->equals('votingDay', $votingDay)
			)
		);
		return $query->execute()->getFirst();
	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 * @return object
	 */
	public function findOneProcessedByBusinessUser(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('businessuser', $businessUser),
				$query->greaterThan('processed', 0)
			)
		);
		$query->setOrderings(array(
			'votingDay.votingDate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
		));
		return $query->execute()->getFirst();
	}

	/**
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findApprovedDatasetsByVotingDay(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('votingDay', $votingDay),
				$query->greaterThan('processed', 0)
			)
		);
		return $query->execute();
	}

}
?>