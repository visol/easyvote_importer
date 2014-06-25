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
use Visol\EasyvoteImporter\Utility\ExcelUtility;


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
	 * blacklistRepository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\BlacklistRepository
	 * @inject
	 */
	protected $blacklistRepository;

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
		if (!$this->isUserLoggedIn()) {
			// link to current page for redirect_url
			$currentPage = (int)$GLOBALS['TSFE']->id;
			$currentPageUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->setTargetPageUid($currentPage)->build();
			// link to login page
			$loginPage = (int)$this->settings['loginPid'];
			$loginPageUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->setTargetPageUid($loginPage)->setArguments(array('redirect_url' => $currentPageUri))->build();
			// redirect to login page
			$this->redirectToUri($loginPageUri, 0, 401);
		}

		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			$this->redirect('adminIndex');
		} else {
			$this->redirect('cityIndex');
		}

	}

	/**
	 * action index
	 *
	 * @return void
	 */
	public function logoutAction() {
		// link to login page
		$loginPage = (int)$this->settings['loginPid'];
		$loginPageUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->setTargetPageUid($loginPage)->setArguments(array('logintype' => 'logout'))->build();
		// redirect to login page
		$this->redirectToUri($loginPageUri, 0, 301);
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

			$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDayUid);

			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
				$businessUser = $this->businessUserRepository->findByUid($cityUid);
			} else {
				/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
				$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			}

			$votingDateAgeStart = clone $votingDay->getVotingDate();
			$lastAllowedBirthdate = $votingDateAgeStart->modify('-' . $businessUser->getTargetGroupStart() . ' years -1 day');

			$votingDateAgeEnd = clone $votingDay->getVotingDate();
			$firstAllowedBirthdate = $votingDateAgeEnd->modify('-' . $businessUser->getTargetGroupEnd() . ' years');

			$this->view->assignMultiple(array(
				'businessUser' => $businessUser,
				'votingDay' => $votingDay,
				'lastAllowedBirthdate' => $lastAllowedBirthdate,
				'firstAllowedBirthdate' => $firstAllowedBirthdate
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
			$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid((int)$request['votingDay']);

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

		// get business user
		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->request->getArgument('city');
		} else {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}

		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {

			// Check if it's user's dataset and redirect to home on access violation
			if ($businessUser->getUid() !== $dataset->getBusinessuser()->getUid()) {
				$message = 'Zugriff verweigert.';
				$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		// get labels and first row of datasets file
		$tableData = \Visol\EasyvoteImporter\Utility\ExcelUtility::getLabelsAndFirstRowFromDataset($dataset);

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

		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			// Check if it's user's dataset and redirect to home on access violation
			if ($businessUser->getUid() !== $dataset->getBusinessuser()->getUid()) {
				$message = 'Zugriff verweigert.';
				$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

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

		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			// Check if it's user's dataset and redirect to home on access violation
			if ($businessUser->getUid() !== $dataset->getBusinessuser()->getUid()) {
				$message = 'Zugriff verweigert.';
				$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

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
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function checkImportAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		// get business user
		/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
		$businessUser = $this->businessUserRepository->findByUid($dataset->getBusinessuser()->getUid());

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
	 * action new blacklist (item)
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist
	 * @dontvalidate $newBlacklist
	 * @return void
	 */
	public function newBlacklistAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist = NULL) {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		// remove error message from Extbase (background on fields that didn't pass validation is sufficient)
		$this->flashMessageContainer->flush();
		$this->view->assign('newBlacklist', $newBlacklist);
	}

	/**
	 * action create blacklist (item)
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist
	 * @return void
	 */
	public function createBlacklistAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist) {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->blacklistRepository->add($newBlacklist);
		$this->flashMessageContainer->add('Die Person wurde zur Blacklist hinzugefügt.');
		$this->redirect('listBlacklist');
	}

	/**
	 * action list blacklist
	 *
	 * @return void
	 */
	public function listBlacklistAction() {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$blacklist = $this->blacklistRepository->findAll();
		$this->view->assign('blacklist', $blacklist);
	}

	/**
	 * action edit blacklist (item)
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist
	 */
	public function editBlacklistAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist) {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->flashMessageContainer->flush();
		$blacklist = $this->blacklistRepository->findByUid($blacklist);
		$this->view->assign('blacklist', $blacklist);
	}

	/**
	 * action update blacklist (item)
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist
	 */
	public function updateBlacklistAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist) {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->blacklistRepository->update($blacklist);
		$this->flashMessageContainer->add('Der Blacklist-Eintrag wurde aktualisiert.');
		$this->redirect('listBlacklist');
	}

	/**
	 * action delete blacklist (item)
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist
	 * @return void
	 */
	public function deleteBlacklistAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $blacklist) {
		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			$message = 'Zugriff verweigert.';
			$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->flashMessageContainer->flush();
		$this->blacklistRepository->remove($blacklist);
		$this->flashMessageContainer->add('Person wurde aus der Blacklist entfernt.');
		$this->redirect('listBlacklist');
	}

	/**
	 * list voting days and display export functionality
	 */
	public function listExportAction() {
		/** @var \Visol\Easyvote\Domain\Model\VotingDay $votingDays */
		$votingDays = $this->votingDayRepository->findUploadAllowedVotingDays();

		$exportInformation = array();
		$i = 0;
		foreach ($votingDays as $votingDay) {
			$exportInformation[$i]['votingDay'] = $votingDay;
			$exportInformation[$i]['datasetCount'] = $this->datasetRepository->findApprovedDatasetsByVotingDay($votingDay)->count();
			$exportInformation[$i]['businessUserCount'] = $this->businessUserRepository->findByUsergroup($this->settings['cityFeUserGroup'])->count();
			$exportInformation[$i]['blacklistCount'] = $this->addressRepository->findByVotingDayAndBlacklist($votingDay)->count();
			$exportInformation[$i]['importedAddresses'] = $this->addressRepository->findByVotingDay($votingDay)->count();
			$i++;
		}
		$this->view->assign('exportInformation', $exportInformation);
	}

	/**
	 * Apply the blacklist to the addresses for a given voting day
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 */
	public function applyBlacklistAction(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$blacklist = $this->blacklistRepository->findAll();
		$blacklistCount = $blacklist->count();
		$addToBlacklistCount = 0;
		foreach ($blacklist as $blacklistItem) {
			// find the corresponding address for a blacklist item
			$address = $this->addressRepository->findBlacklistMatchByVotingDay($blacklistItem, $votingDay);
			if ($address instanceof \Visol\EasyvoteImporter\Domain\Model\Address) {
				// set it to blacklisted, if found and update the address
				$address->setBlacklisted(TRUE);
				$this->addressRepository->update($address);
				$addToBlacklistCount++;
			}
		}

		$message = 'Total in der Blacklist: ' . $blacklistCount . ' Adressen.<br />' . $addToBlacklistCount . ' Adresse(n) wurden aufgrund der Blacklist vom Versand ausgeschlossen.';
		$this->flashMessageContainer->add($message);
		$this->redirect('listExport');

	}

	/**
	 * Export all addresses for a voting day
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 */
	public function performExportAction(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$addresses = $this->addressRepository->findAllNotBlacklistedByVotingDay($votingDay);

		ExcelUtility::pushExcelExportFromAddresses($addresses, $votingDay);
		die();

	}

	/**
	 * Export all addresses for a voting day
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 */
	public function reportExportAction(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$addresses = $this->addressRepository->findAllNotBlacklistedByVotingDay($votingDay);
		$this->view->assignMultiple(array(
			'addresses' => $addresses,
			'votingDay' => $votingDay
		));
	}

	/**
	 * Remove all addresses for a voting day
	 *
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 */
	public function removeExportAction(\Visol\Easyvote\Domain\Model\VotingDay $votingDay) {
		$addresses = $this->addressRepository->findByVotingDay($votingDay);
		$addressCount = $addresses->count();
		foreach ($addresses as $address) {
			$this->addressRepository->remove($address);
		}
		// upload no longer allowed
		$votingDay->setUploadAllowed(FALSE);
		$this->votingDayRepository->update($votingDay);
		$message = $addressCount . ' Adressen gelöscht.';
		$this->flashMessageContainer->add($message);
		$this->redirect('listExport');
	}

	/**
	 * Edit a business user
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 */
	public function editBusinessUserAction(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser) {
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			if (!$this->isRequestedUserLoggedInUser($businessUser)) {
				$message = 'Zugriff verweigert.';
				$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		$this->flashMessageContainer->flush();
		$businessUser = $this->businessUserRepository->findByUid($businessUser);
		$this->view->assign('businessUser', $businessUser);
	}

	/**
	 * Update a business user
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 */
	public function updateBusinessUserAction(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser) {
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			if (!$this->isRequestedUserLoggedInUser($businessUser)) {
				$message = 'Zugriff verweigert.';
				$this->flashMessageContainer->add($message, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		$this->businessUserRepository->update($businessUser);
		$this->flashMessageContainer->add('Ihre Kontaktdaten wurden aktualisiert.');
		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			$this->redirect('cityIndex', NULL, NULL, array('city' => $businessUser->getUid()));
		} else {
			$this->redirect('cityIndex');
		}

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

	/**
	 * Checks if a user is logged in
	 *
	 * @return boolean
	 */
	public function isUserLoggedIn() {
		if ((int)$GLOBALS['TSFE']->fe_user->user['uid'] > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Checks if a user that is requested is the user logged in
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 * @return boolean
	 */
	public function isRequestedUserLoggedInUser(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser) {
		if ((int)$GLOBALS['TSFE']->fe_user->user['uid'] === $businessUser->getUid()) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>