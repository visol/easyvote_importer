<?php
namespace Visol\EasyvoteImporter\Controller;

// Composer Autoloader
require_once(PATH_site . 'Packages/Libraries/autoload.php');

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Lorenz Ulrich <lorenz.ulrich@visol.ch>, visol digitale Dienstleistungen GmbH
 *  
 *  All rights reserved
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
use GuzzleHttp\Psr7\Response;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Visol\EasyvoteImporter\Domain\Model\Blacklist;
use Visol\EasyvoteImporter\Domain\Model\Dataset;
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
	 * businessUser repository
	 *
	 * @var \Visol\EasyvoteImporter\Domain\Repository\BusinessUserRepository
	 * @inject
	 */
	protected $businessUserRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $frontendUserGroupRepository;

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

		$latestProcessedDataset = $this->datasetRepository->findOneProcessedByBusinessUser($businessUser);

		$this->view->assignMultiple(array(
			'businessUser' => $businessUser,
			'votingDays' => $votingDays,
			'datasets' => $datasets,
			'latestProcessedDataset' => $latestProcessedDataset
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
		$loginPageUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->setUseCacheHash(FALSE)->setTargetPageUid($loginPage)->setArguments(array('logintype' => 'logout'))->build();
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

			// Whether or not the age range box is displayed in the frontend
			$displayAgeRange = TRUE;

			if (!empty($businessUser->getTargetGroupEnd())) {
				$votingDateAgeEnd = clone $votingDay->getVotingDate();
				$firstAllowedBirthdate = $votingDateAgeEnd->modify('-' . $businessUser->getTargetGroupEnd() . ' years -1 year -1 day');
				$this->view->assign('firstAllowedBirthdate', $firstAllowedBirthdate);
			} else {
				$displayAgeRange = FALSE;
			}

			if (!empty($businessUser->getTargetGroupStart())) {
				$votingDateAgeStart = clone $votingDay->getVotingDate();
				$lastAllowedBirthdate = $votingDateAgeStart->modify('-' . $businessUser->getTargetGroupStart() . ' years');
				$this->view->assign('lastAllowedBirthdate', $lastAllowedBirthdate);
			} else {
				$displayAgeRange = FALSE;
			}

			$this->view->assignMultiple(array(
				'businessUser' => $businessUser,
				'votingDay' => $votingDay,
				'displayAgeRange' => $displayAgeRange
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

				$error = LocalizationUtility::translate('performUploadAction.fileFormatError', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($error, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

				$this->checkUserIsAdmin();

				if ($this->userIsAdmin) {
					$cityUid = $this->request->getArgument('city');
					$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
				} else {
					$this->redirect('cityIndex');
				}

			}

			// get other arguments
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
				$error = LocalizationUtility::translate('performUploadAction.existingMatchingDataset', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($error, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

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
			$dataset = $this->objectManager->get('Visol\EasyvoteImporter\Domain\Model\Dataset');
			$dataset->setVotingDay($votingDay);
			$dataset->setFile(basename($fileName));
			$dataset->setFirstrowColumnnames((boolean)$request['firstLineContainsLabels']);

			$this->datasetRepository->add($dataset);
			$businessUser->addDataset($dataset);
			$this->businessUserRepository->update($businessUser);
			$this->persistenceManager->persistAll();
			$this->redirect('assign', NULL, NULL, array('dataset' => $dataset, 'city' => $businessUser));

		} else {
			$error = LocalizationUtility::translate('performUploadAction.noDocumentUploadedError', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($error, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

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
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
	 * Removes a dataset and all adresses connected to this dataset
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function removeDatasetAndAddressesAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {
		// get business user
		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			// Remove addresses
			$affectedAddresses = $this->addressRepository->removeByDataset($dataset);
			// Remove dataset
			$this->datasetRepository->remove($dataset);
			$this->persistenceManager->persistAll();
			$this->addFlashMessage(sprintf('Dataset und %s Adressen wurden gelöscht.', $affectedAddresses), '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			$this->redirect('cityIndex', NULL, NULL, array('city' => $this->request->getArgument('city')));
		} else {
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function approveAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		// security check
		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			// Check if it's user's dataset and redirect to home on access violation
			if ($businessUser->getUid() !== $dataset->getBusinessuser()->getUid()) {
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		$arguments = $this->request->getArguments();
		$columns = array();
		$columns['salutation'] = $arguments['columns-salutation'];
		$columns['name'] = $arguments['columns-name'];
		$columns['address'] = $arguments['columns-address'];
		$columns['city'] = $arguments['columns-city'];

		// salutation is not a mandatory value
		if (!empty($columns['name']) && !empty($columns['address']) && !empty($columns['city'])) {
			foreach ($columns as $key => $column) {
				// transform comma-separated value to array, remove empty items
				$columns[$key] = GeneralUtility::trimExplode(',', $column, TRUE);
			}
			// save serialized column configuration to database
			$dataset->setColumnConfiguration(serialize($columns));
			$this->datasetRepository->update($dataset);
			$this->persistenceManager->persistAll();

			// report success and go back to index
			$header = LocalizationUtility::translate('approveAction.successHeader', $this->request->getControllerExtensionName());
			$message = LocalizationUtility::translate('approveAction.successMessage', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, $header, \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			$this->checkUserIsAdmin();
			if ($this->userIsAdmin) {
				$cityUid = $this->request->getArgument('city');
				$this->redirect('cityIndex', NULL, NULL, array('city' => $cityUid));
			} else {
				$this->redirect('cityIndex');
			}
		} else {
			$header = LocalizationUtility::translate('approveAction.failHeader', $this->request->getControllerExtensionName());
			$message = LocalizationUtility::translate('approveAction.failMessage', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, $header, \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		$absoluteFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		if (unlink($absoluteFileName)) {
			$this->datasetRepository->remove($dataset);
			$this->persistenceManager->persistAll();
			// report success and go back to index
			$header = LocalizationUtility::translate('removeAction.successHeader', $this->request->getControllerExtensionName());
			$message = LocalizationUtility::translate('removeAction.successMessage', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, $header, \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			$this->redirect('index');
		} else {
			$message = LocalizationUtility::translate('removeAction.failMessage', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
			$address = $this->objectManager->get('Visol\EasyvoteImporter\Domain\Model\Address');
			$address->setVotingDay($dataset->getVotingDay()->getUid());
			$address->setDataset($dataset);
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

		// set the number of imported addresses
		$dataset->setImportedAddresses(count($importData));

		$success = 'Die Adressen wurden erfolgreich importiert.';
		$this->flashMessageContainer->add($success, 'Aktion erfolgreich abgeschlossen', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);

		$absoluteFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		if (unlink($absoluteFileName)) {
			// remove file reference from dataset
			$dataset->setFile('');
			$dataset->setProcessed(time());
			// report success and go back to index
			$error = 'Originaldatei wurde erfolgreich gelöscht.';
			$this->flashMessageContainer->add($error, 'Datenschutz-Info', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
		} else {
			$error = 'Die Originaldatei konnte nicht gelöscht werden.';
			$this->flashMessageContainer->add($error, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

		}

		$this->datasetRepository->update($dataset);
		$this->persistenceManager->persistAll();

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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->blacklistRepository->add($newBlacklist);
		$this->flashMessageContainer->add('Die Person wurde zur Robinsonliste hinzugefügt.');
		$this->redirect('listBlacklist');
	}

    /**
     * An anonymous user adds a new blacklist item
     *
     * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist
     * @return void
     */
    public function newBlacklistPublicAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist = NULL) {
        $this->view->assign('newBlacklist', $newBlacklist);
    }

    /**
     * Creates a new blacklist item
     *
     * @param \Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist
     * @return void
     */
    public function createBlacklistPublicAction(\Visol\EasyvoteImporter\Domain\Model\Blacklist $newBlacklist) {
        $this->blacklistRepository->add($newBlacklist);
		$this->submitBlacklistEntryToCrm($newBlacklist);
        if (!empty($newBlacklist->getComment())) {
            // We have a comment, therefore easyvote needs to be informed
            /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
            $message = GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
            $message->setTo(array('info@easyvote.ch' => 'easyvote'));
            $message->setFrom(array('info@easyvote.ch' => 'easyvote'));
            $message->setSubject('Data Manager: Neuer Eintrag in der Robinsonliste mit Kommentar');
            $content = '<html><body><p><strong>Person:</strong> %s %s, %s, %s<br /><strong>Grund:</strong> %s<br /><strong>Kommentar:</strong> %s</p></body></html>';
            $reasonText = !empty($newBlacklist->getReason()) ? LocalizationUtility::translate('blacklist.reason.' . $newBlacklist->getReason(), $this->request->getControllerExtensionName()) : '';
            $parsedContent = sprintf($content, $newBlacklist->getFirstName(), $newBlacklist->getLastName(), $newBlacklist->getStreet(), $newBlacklist->getZipCode(), $reasonText, $newBlacklist->getComment());
            $message->setBody($parsedContent, 'text/html');
            $message->send();
        }
        $this->redirect('createdBlacklistPublic');
    }

	/**
	 * Submits a Blacklist entry to the CRM using the CRM's Web API
	 *
	 * @param Blacklist $blacklist
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	protected function submitBlacklistEntryToCrm(Blacklist $blacklist) {
		$url = vsprintf('%s/data-manager/robinson-list', [
			$this->settings['crmWebApiBaseUri']
		]);

		$robinsonListEntry = [
			'firstName' => $blacklist->getFirstName(),
			'lastName' => $blacklist->getLastName(),
			'street' => $blacklist->getStreet(),
			'zipCode' => $blacklist->getZipCode(),
			'reason' => $blacklist->getReason(),
			'comment' => $blacklist->getComment()
		];

		$client = new \GuzzleHttp\Client();
		/** @var Response $response */
		$response = $client->post($url, [
			'json' => [
				'robinsonList' => $robinsonListEntry
			],
			'http_errors' => false
		]);
		if ($response->getStatusCode() === 201) {
			return;
		} else {
			$this->redirect('errorBlacklistPublic');
		}
	}

    /**
     * Success message when a new blacklist item was created
     */
    public function createdBlacklistPublicAction() {
    }

    /**
     * Error message when a new blacklist item was created
     */
    public function errorBlacklistPublicAction() {
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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->blacklistRepository->update($blacklist);
		$this->flashMessageContainer->add('Der Robinsonlisten-Eintrag wurde aktualisiert.');
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
			$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
			$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('cityIndex');
		}

		$this->flashMessageContainer->flush();
		$this->blacklistRepository->remove($blacklist);
		$this->flashMessageContainer->add('Person wurde aus der Robinsonliste entfernt.');
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
	 * @param int $votingDay
	 */
	public function applyBlacklistAction($votingDay) {
		$blacklist = $this->blacklistRepository->findAll();
		$blacklistCount = $blacklist->count();
		$addToBlacklistCount = 0;
		$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDay);
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

		$message = 'Total in der Robinsonliste: ' . $blacklistCount . ' Adressen.<br />' . $addToBlacklistCount . ' Adresse(n) wurden aufgrund der Robinsonliste vom Versand ausgeschlossen.';
		$this->flashMessageContainer->add($message);
		$this->redirect('listExport');

	}

	/**
	 * Export all addresses for a voting day
	 *
	 * @param int $votingDay
	 */
	public function performExportAction($votingDay) {
		$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDay);
		$addresses = $this->addressRepository->findAllNotBlacklistedByVotingDayArray($votingDay);

		ExcelUtility::pushExcelExportFromAddresses($addresses, $votingDay);
		die();

	}

	/**
	 * Export all addresses for a voting day
	 *
	 * @param int $votingDay
	 */
	public function reportExportAction($votingDay) {
		$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDay);
		$addresses = $this->addressRepository->findAllNotBlacklistedByVotingDay($votingDay);
		$this->view->assignMultiple(array(
			'addresses' => $addresses,
			'votingDay' => $votingDay
		));
	}

	/**
	 * Remove all addresses for a voting day
	 *
	 * @param int $votingDay
	 */
	public function removeExportAction($votingDay) {
		$votingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDay);
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
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		$this->businessUserRepository->update($businessUser);
		$message = LocalizationUtility::translate('updateBusinessUserAction.successMessage', $this->request->getControllerExtensionName());
		$this->flashMessageContainer->add($message);
		$this->checkUserIsAdmin();
		if ($this->userIsAdmin) {
			$this->redirect('cityIndex', NULL, NULL, array('city' => $businessUser->getUid()));
		} else {
			$this->redirect('cityIndex');
		}

	}

	/**
	 * List statistics
	 */
	public function listStatsAction() {
		$statsData = array();

		$votingDays = $this->votingDayRepository->findAll()->toArray();
		foreach ($votingDays as $key => $votingDay) {
			if ($this->datasetRepository->countByVotingDay($votingDay) === 0) {
				unset($votingDays[$key]);
			}
		}

		$cityFrontendUserGroup = $this->frontendUserGroupRepository->findByUid((int)$this->settings['cityFeUserGroup']);
		$businessUsers = $this->businessUserRepository->findByUsergroup($cityFrontendUserGroup);
		foreach ($businessUsers as $businessUser) {
			/** @var $businessUser \Visol\EasyvoteImporter\Domain\Model\BusinessUser */
			$statsData[$businessUser->getUid()]['businessuser']['customerNumber'] = $businessUser->getCustomerNumber();
			$statsData[$businessUser->getUid()]['businessuser']['company'] = $businessUser->getCompany();
			foreach ($votingDays as $votingDay) {
				/** @var $votingDay \Visol\Easyvote\Domain\Model\VotingDay */
				/** @var $dataset \Visol\EasyvoteImporter\Domain\Model\Dataset */
				$dataset = $this->datasetRepository->findDatasetByBusinessUserAndVotingDate($businessUser, $votingDay);
				if ($dataset instanceof \Visol\EasyvoteImporter\Domain\Model\Dataset) {
					$statsData[$businessUser->getUid()]['datasets'][$votingDay->getUid()]['importedAddresses'] = $dataset->getImportedAddresses();
				} else {
					$statsData[$businessUser->getUid()]['datasets'][$votingDay->getUid()]['importedAddresses'] = 0;
				}
			}
		}

		$this->view->assign('statsData', $statsData);
		$this->view->assign('votingDays', $votingDays);

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser
	 * @param int $votingDay
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $sourceDataset
	 * @
	 */
	public function copyAddressesFromOtherVotingDayAction(\Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser, $votingDay, \Visol\EasyvoteImporter\Domain\Model\Dataset $sourceDataset) {

		$targetVotingDay = $this->votingDayRepository->findVisibleAndHiddenByUid($votingDay);

		$this->checkUserIsAdmin();
		if (!$this->userIsAdmin) {
			if (!$this->isRequestedUserLoggedInUser($businessUser)) {
				$message = LocalizationUtility::translate('flashMessage.accessDenied', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, LocalizationUtility::translate('flashMessage.errorHeader', $this->request->getControllerExtensionName()), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('cityIndex');
			}
		}

		/* @var $dataset \Visol\EasyvoteImporter\Domain\Model\Dataset */
		$dataset = $this->objectManager->get('Visol\EasyvoteImporter\Domain\Model\Dataset');
		$dataset->setBusinessuser($businessUser);
		$dataset->setVotingDay($targetVotingDay);
		$dataset->setSourceDataset($sourceDataset);
		$this->datasetRepository->add($dataset);
		$businessUser->addDataset($dataset);
		$this->persistenceManager->persistAll();

		$addresses = $this->addressRepository->findByDataset($sourceDataset);
		$i = 1;
		foreach ($addresses as $address) {
			/** @var \Visol\EasyvoteImporter\Domain\Model\Address $address */
			/** @var \Visol\EasyvoteImporter\Domain\Model\Address $newAddress */
			$newAddress = $this->objectManager->get('Visol\EasyvoteImporter\Domain\Model\Address');
			$newAddress->setVotingDay($targetVotingDay);
			$newAddress->setDataset($dataset);
			$newAddress->setBusinessuser($businessUser);
			$newAddress->setImportFileName($address->getImportFileName());
			$newAddress->setBlacklisted($address->getBlacklisted());
			$newAddress->setSalutation($address->getSalutation());
			$newAddress->setName($address->getName());
			$newAddress->setStreet($address->getStreet());
			$newAddress->setCity($address->getCity());
			$this->addressRepository->add($newAddress);
			// persist after each 50 items
			if ($i % 50 == 0) {
				$this->persistenceManager->persistAll();
			}
			$i++;
		}

		// update dataset
		$dataset->setImportedAddresses($addresses->count());
		$dataset->setProcessed(time());

		// last persist
		$this->datasetRepository->update($dataset);
		$this->persistenceManager->persistAll();

		// redirect to cityIndex
		$message = $addresses->count() . ' ' . LocalizationUtility::translate('copyAddressesFromOtherVotingDayAction.addressesCopied', $this->request->getControllerExtensionName());
		$this->flashMessageContainer->add($message);
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