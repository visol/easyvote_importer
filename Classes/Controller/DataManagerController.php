<?php
namespace Visol\EasyvoteImporter\Controller;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DataManagerController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * frontendUser repository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * businessUser repository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\BusinessUserRepository
	 * @inject
	 */
	protected $businessUserRepository;

	/**
	 * votingDayRepository
	 *
	 * @var \Visol\Easyvote\Domain\Repository\VotingDayRepository
	 * @inject
	 */
	protected $votingDayRepository;

	/**
	 * datasetRepository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\DatasetRepository
	 * @inject
	 */
	protected $datasetRepository;

	/**
	 * addressRepository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\AddressRepository
	 * @inject
	 */
	protected $addressRepository;

	/**
	 * Determines if the currently logged in user is an admin
	 *
	 * @var boolean
	 */
	protected $userIsAdmin = FALSE;


	/**
	 * action index
	 *
	 * @return void
	 */
	public function adminIndexAction() {
		$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

		$cities = $this->businessUserRepository->findByUsergroup($this->settings['cityFeUserGroup']);

		$votingDays = $this->votingDayRepository->findUploadAllowedVotingDays();

		$this->view->assignMultiple(array(
			'businessUser' => $businessUser,
			'cities' => $cities,
			'votingDays' => $votingDays,
		));
	}

	/**
	 * action cityIndex
	 *
	 * @return void
	 */
	public function cityIndexAction() {
		$this->checkUserIsAdmin();

		if ($this->userIsAdmin) {
			$cityUid = $this->request->getArgument('city');
			$businessUser = $this->businessUserRepository->findByUid($cityUid);
		} else {
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}

		$votingDays = $this->votingDayRepository->findUploadAllowedVotingDays();
		$datasets = array();
		foreach ($votingDays as $votingDay) {
			$dataset = $this->datasetRepository->findDatasetByBusinessUserAndVotingDate($businessUser, $votingDay);
			if (count($dataset)) {
				$datasets[] = $dataset;
			} else {
				$datasets[] = '';
			}
		}

		$this->view->assignMultiple(array(
			'businessUser' => $businessUser,
			'votingDays' => $votingDays,
			'datasets' => $datasets
		));
	}

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->checkUserIsAdmin();

		if ($this->userIsAdmin) {
			$this->redirect('adminIndex');
		} else {
			$this->redirect('cityIndex');
		}

	}



	/**
	 * action uploadList
	 *
	 * @return void
	 */
	public function uploadListAction() {
		$arguments = $this->request->getArguments();
		if (is_array($arguments['votingDay'])) {
			$votingDayUid = (int)key($arguments['votingDay']);
			$votingDay = $this->votingDayRepository->findByUid($votingDayUid);

			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				$businessUser = $this->businessUserRepository->findByUid($cityUid);
			} else {
				$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			}

			$this->view->assignMultiple(array(
				'businessUser' => $businessUser,
				'votingDay' => $votingDay
			));

		}
	}

	/**
	 * action upload
	 *
	 * @return void
	 */
	public function performUploadAction() {

		$pluginNamespace = 'tx_easyvoteimporter_datamanager';
		if ($_FILES[$pluginNamespace] && !empty($_FILES[$pluginNamespace]['tmp_name']['easyvoteData'])) {

			/** @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility $basicFileUtility */
			$basicFileUtility = GeneralUtility::makeInstance('t3lib_basicFileFunctions');

			$cleanFileName = $basicFileUtility->cleanFileName($_FILES[$pluginNamespace]['name']['easyvoteData']);

			// check for allowed file extensions and early return if file type is not allowed
			$allowedFileExtensions =  array('xls','xlsx' ,'csv');
			$fileExtension = pathinfo($cleanFileName, PATHINFO_EXTENSION);
			if (!in_array($fileExtension, $allowedFileExtensions) ) {
				$error = 'Es können nur folgende Dateiformate hochgeladen werden: Excel 2003 (.xls), Excel 2007 und neuer (.xlsx), CSV (.csv).';
				$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

				$this->checkUserIsAdmin();
				if ($this->userIsAdmin) {
					$cityUid = $this->request->getArgument('city');
					$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
				} else {
					$this->redirect('cityIndex');
				}

			}

			// get other requests
			$request = $this->request->getArguments();

			// get voting day object
			/** @var \Visol\Easyvote\Domain\Model\VotingDay $votingDay */
			$votingDay = $this->votingDayRepository->findByUid((int)$request['votingDay']);

			// get business user
			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
				$businessUser = $this->businessUserRepository->findByUid($cityUid);
			} else {
				/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
				$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			}

			$existingMatchingDataset = $this->datasetRepository->findDatasetByBusinessUserAndVotingDate($businessUser, $votingDay);
			if (count($existingMatchingDataset)) {
				// a dataset exists for the current voting day and user, therefore we cannot add another one
				$error = 'Für diesen Abstimmungstag wurde bereits eine Liste hochgeladen.';
				$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

				$this->checkUserIsAdmin();
				if ($this->userIsAdmin) {
					$cityUid = $this->request->getArgument('city');
					$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
				} else {
					$this->redirect('cityIndex');
				}
			}

			// get unique name and move file to upload directory, must be protected by .htaccess!
			$fileName = $basicFileUtility->getUniqueName(
				$cleanFileName,
				GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/')
			);
			GeneralUtility::upload_copy_move($_FILES[$pluginNamespace]['tmp_name']['easyvoteData'], $fileName);

			/** @var \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset */
			$dataset = $this->objectManager->create('Visol\EasyvoteImporter\Domain\Model\Dataset');
			$dataset->setVotingDay($votingDay);
			$dataset->setFile(basename($fileName));
			$dataset->setFirstrowColumnnames((boolean)$request['firstLineContainsLabels']);

			$businessUser->addDataset($dataset);

			$this->persistenceManager->persistAll();
			$this->redirect('assign', NULL, NULL, array('dataset' => $dataset, 'city' => $businessUser));

		} else {
			$error = 'Es wurde kein Dokument ausgewählt. Die Dateneingabe war nicht erfolgreich.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
			} else {
				$this->redirect('cityIndex');
			}
		}

	}

	/**
	 * action assign
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function assignAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		$tableData = \Visol\EasyvoteImporter\Utility\ExcelUtility::getLabelsAndFirstRowFromDataset($dataset);

		// get business user
		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->request->getArgument('city');
		} else {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}

		$this->view->assignMultiple(array(
			'businessUser' => $businessUser,
			'tableData' => $tableData,
			'dataset' => $dataset,
		));

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @dontverifyrequesthash
	 */
	public function approveAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		$arguments = $this->request->getArguments();
		$columns = array();
		$columns['salutation'] = $arguments['columns-salutation'];
		$columns['name'] = $arguments['columns-name'];
		$columns['address'] = $arguments['columns-address'];
		$columns['city'] = $arguments['columns-city'];

		if (!empty($columns['salutation']) && !empty($columns['name']) && !empty($columns['address']) && !empty($columns['name'])) {
			foreach ($columns as $key => $column) {
				// transform comma-separated value to array, remove empty items
				$columns[$key] = GeneralUtility::trimExplode(',', $column, TRUE);
			}
			// save serialized column configuration to database
			$dataset->setColumnConfiguration(serialize($columns));
			$this->datasetRepository->update($dataset);
			$this->persistenceManager->persistAll();

			// report success and go back to index
			$error = 'Ihre Daten und die Spaltenzuweisung wurden gespeichert. Besten Dank.';
			$this->flashMessageContainer->add($error, 'Daten-Upload erfolgreich', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
			} else {
				$this->redirect('cityIndex');
			}
		} else {
			$error = 'Es wurden nicht alle benötigten Felder zugewiesen. Die Dateneingabe wurde abgebrochen.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
			} else {
				$this->redirect('cityIndex');
			}
		}

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function removeAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		$absoluteFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		if (unlink($absoluteFileName)) {
			$this->datasetRepository->remove($dataset);
			$this->persistenceManager->persistAll();
			// report success and go back to index
			$error = 'Ihr Dokument wurde gelöscht.';
			$this->flashMessageContainer->add($error, 'Daten gelöscht', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			$this->redirect('index');
		} else {
			$error = 'Ihr Dokument konnte nicht gelöscht werden. Bitte wenden Sie sich an unser Team.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('index');
		}
	}

	/**
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function checkImportAction(\Visol\Easyvote\Domain\Model\VotingDay $votingDay, \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		// get business user
		/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
		$businessUser = $this->businessUserRepository->findByUid($dataset->getBusinessuser());

		// get 10 rows for preview
		$previewData = \Visol\EasyvoteImporter\Utility\ExcelUtility::getAddressDataFromDataset($dataset, 10);

		$this->view->assignMultiple(array(
			'businessUser' => $businessUser,
			'dataset' => $dataset,
			'previewData' => $previewData
		));

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function performImportAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		// get business user
		/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
		$businessUser = $this->businessUserRepository->findByUid($dataset->getBusinessuser());

		// get all data from dataset
		$importData = \Visol\EasyvoteImporter\Utility\ExcelUtility::getAddressDataFromDataset($dataset);

		$i = 1;
		foreach ($importData as $record) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\Address $address */
			$address = $this->objectManager->create('Visol\EasyvoteImporter\Domain\Model\Address');
			$address->setVotingDay($dataset->getVotingDay()->getUid());
			$address->setImportFileName($dataset->getFile());
			$address->setSalutation($record['salutation']);
			$address->setName($record['name']);
			$address->setStreet($record['address']);
			$address->setCity($record['city']);
			$address->setCustomerNumber($businessUser->getCustomerNumber());
			$this->addressRepository->add($address);
			// persist after each 50 items
			if ($i % 50 == 0) {
				$this->persistenceManager->persistAll();
			}
			$i++;
		}

		$this->persistenceManager->persistAll();
		$success = 'Die Adressen wurden erfolgreich importiert.';
		$this->flashMessageContainer->add($success, 'Aktion erfolgreich abgeschlossen', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);

		$absoluteFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		if (unlink($absoluteFileName)) {
			// remove file reference from dataset
			$dataset->setFile('');
			$dataset->setProcessed(time());
			$this->datasetRepository->update($dataset);
			$this->persistenceManager->persistAll();
			// report success and go back to index
			$error = 'Originaldatei wurde erfolgreich gelöscht.';
			$this->flashMessageContainer->add($error, 'Datenschutz-Info', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
		} else {
			$error = 'Die Originaldatei konnte nicht gelöscht werden.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

		}

		$this->redirect('adminIndex');

	}

	/**
	 * Checks if the current user is an admin
	 *
	 * @return void
	 */
	public function checkUserIsAdmin() {

		if (in_array($this->settings['adminFeUserGroup'], $GLOBALS['TSFE']->fe_user->groupData['uid'])) {
			$this->userIsAdmin = TRUE;
		}

	}

}
?>