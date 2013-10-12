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
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 *
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DataUploaderController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {

		if (is_int((int)$GLOBALS['TSFE']->fe_user->user['uid'])) {
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

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
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
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

		if ($_FILES['tx_easyvoteimporter_datauploader'] && !empty($_FILES['tx_easyvoteimporter_datauploader']['tmp_name']['easyvoteData'])) {

			/** @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility $basicFileUtility */
			$basicFileUtility = GeneralUtility::makeInstance('t3lib_basicFileFunctions');

			$cleanFileName = $basicFileUtility->cleanFileName($_FILES['tx_easyvoteimporter_datauploader']['name']['easyvoteData']);

			// check for allowed file extensions and early return if file type is not allowed
			$allowedFileExtensions =  array('xls','xlsx' ,'csv');
			$fileExtension = pathinfo($cleanFileName, PATHINFO_EXTENSION);
			if (!in_array($fileExtension, $allowedFileExtensions) ) {
				$error = 'Es können nur folgende Dateiformate hochgeladen werden: Excel 2003 (.xls), Excel 2007 und neuer (.xlsx), CSV (.csv).';
				$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->redirect('index');
			}

			// get unique name and move file to upload directory, must be protected by .htaccess!
			$fileName = $basicFileUtility->getUniqueName(
				$cleanFileName,
				GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/')
			);
			GeneralUtility::upload_copy_move($_FILES['tx_easyvoteimporter_datauploader']['tmp_name']['easyvoteData'], $fileName);


			// get other requests
			$request = $this->request->getArguments();

			// get voting day object
			/** @var \Visol\Easyvote\Domain\Model\VotingDay $votingDay */
			$votingDay = $this->votingDayRepository->findByUid((int)$request['votingDay']);

			/** @var \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset */
			$dataset = $this->objectManager->create('Visol\EasyvoteImporter\Domain\Model\Dataset');
			$dataset->setVotingDay($votingDay);
			$dataset->setFile(basename($fileName));
			$dataset->setFirstrowColumnnames((boolean)$request['firstLineContainsLabels']);

			/** @var \Visol\EasyvoteImporter\Domain\Model\BusinessUser $businessUser */
			$businessUser = $this->businessUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			$businessUser->addDataset($dataset);

			$this->persistenceManager->persistAll();
			$this->redirect('assign', NULL, NULL, array('dataset' => $dataset));

		} else {
			$error = 'Es wurde kein Dokument ausgewählt. Die Dateneingabe war nicht erfolgreich.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('index');
		}

	}

	/**
	 * action assign
	 *
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 */
	public function assignAction(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

		// detect file type
		$inputFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);

		// open file in matching reader
		$reader = \PHPExcel_IOFactory::createReader($inputFileType);
		$document = $reader->load($inputFileName);

		$sheet = $document->getSheet(0);
		$highestColumn = $sheet->getHighestColumn();

		if ($dataset->getFirstrowColumnnames()) {
			// take the labels from the table
			$headerRow = array_pop($sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE));
			$firstDataRow = array_pop($sheet->rangeToArray('A2:' . $highestColumn . '2', NULL, TRUE, FALSE));
			$tableData = array();
			$index = 0;
			$cycle = 1;
			foreach ($headerRow as $column) {
				$tableData[$index]['name'] = $column;
				$tableData[$index]['columnNumber'] = $cycle;
				$tableData[$index]['firstItem'] = $firstDataRow[$index];
				$index++;
				$cycle++;
			}
		} else {
			// generate a header row
			$firstDataRow = array_pop($sheet->rangeToArray('A2:' . $highestColumn . '2', NULL, TRUE, FALSE));
			$tableData = array();
			$index = 0;
			$cycle = 1;
			foreach ($firstDataRow as $column) {
				$tableData[$index]['name'] = 'Spalte ' . $cycle;
				$tableData[$index]['columnNumber'] = $cycle;
				$tableData[$index]['firstItem'] = $firstDataRow[$index];
				$index++;
				$cycle++;
			}
		}
		$this->view->assignMultiple(array(
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
		$columns['name'] = $arguments['columns-name'];
		$columns['address'] = $arguments['columns-address'];
		$columns['city'] = $arguments['columns-city'];

		if (!empty($columns['name']) && !empty($columns['address']) && !empty($columns['name'])) {
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
			$this->redirect('index');
		} else {
			$error = 'Es wurden nicht alle benötigten Felder zugewiesen. Die Dateneingabe wurde abgebrochen.';
			$this->flashMessageContainer->add($error, 'Fehler', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$this->redirect('index');
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

}
?>