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
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * action upload
	 *
	 * @return void
	 */
	public function uploadAction() {
/*		print('<pre>');
		var_dump($_FILES);
		die();*/



		if ($_FILES['tx_easyvoteimporter_datauploader']) {
			/** @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility $basicFileUtility */
			$basicFileUtility = GeneralUtility::makeInstance('t3lib_basicFileFunctions');

			$cleanFileName= $basicFileUtility->cleanFileName($_FILES['tx_easyvoteimporter_datauploader']['name']['easyvoteData']);

			$fileName = $basicFileUtility->getUniqueName(
				$cleanFileName,
				GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/'));

			GeneralUtility::upload_copy_move(
			$_FILES['tx_easyvoteimporter_datauploader']['tmp_name']['easyvoteData'],
			$fileName);

			$this->redirect('assign');

		}

	}

	public function assignAction() {

		$firstLineContainsLabels = FALSE;

		$inputFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/Gemeinde_Daeniken_September_2013.xls');
		$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
		$reader = \PHPExcel_IOFactory::createReader($inputFileType);
		$document = $reader->load($inputFileName);

		$sheet = $document->getSheet(0);
		$highestColumn = $sheet->getHighestColumn();
		$numberOfColumns = \PHPExcel_Cell::columnIndexFromString($highestColumn);

		if ($firstLineContainsLabels) {
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
		));

/*		print('<pre>');
		var_dump($headerRow);
		print('</pre>');*/

	}

}
?>