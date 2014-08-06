<?php
namespace Visol\EasyvoteImporter\Utility;

/**
 * Created by JetBrains PhpStorm.
 * User: palulrich
 * Date: 01.11.13
 * Time: 12:59
 * To change this template use File | Settings | File Templates.
 */

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExcelUtility {

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @return array
	 */
	public static function getLabelsAndFirstRowFromDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset) {

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

		return $tableData;

	}

	/**
	 * @param \Visol\EasyvoteImporter\Domain\Model\Dataset $dataset
	 * @param bool $limit
	 * @return mixed
	 */
	public static function getAddressDataFromDataset(\Visol\EasyvoteImporter\Domain\Model\Dataset $dataset, $limit = FALSE) {

		// detect file type
		$inputFileName = GeneralUtility::getFileAbsFileName('uploads/tx_easyvoteimporter/' . $dataset->getFile());
		$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);

		// get column configuration
		$columnConfiguration = unserialize($dataset->getColumnConfiguration());
		$columnAssignment = array();
		foreach ($columnConfiguration as $targetFieldName => $targetColumnNumericIdArray) {
			foreach ($targetColumnNumericIdArray as $targetColumnNumericId) {
				$columnAssignment[$targetFieldName][] = \PHPExcel_Cell::stringFromColumnIndex($targetColumnNumericId);
			}
		}

		// open file in matching reader
		$reader = \PHPExcel_IOFactory::createReader($inputFileType);
		$document = $reader->load($inputFileName);

		$sheet = $document->getSheet(0);
		$firstRowNumber = $dataset->getFirstrowColumnnames() ? 2 : 1;

		if ($limit) {
			// only get a limited number of rows, consider if the first column contains row names
			$highestRow = $firstRowNumber === 2 ? (int)$limit + 1 : $limit;
		} else {
			$highestRow = $sheet->getHighestRow();
		}

		$requiredNumberOfNotEmptyCells = 1;
		$assignedData = array();
		for ($i = $firstRowNumber; $i <= $highestRow; $i++) {
			$notEmptyCells = 0;
			foreach ($columnAssignment as $columnName => $columnArray) {
				$assignedData[$i][$columnName] = '';
				foreach ($columnArray as $column) {
					$assignedData[$i][$columnName] .= $sheet->getCell($column . $i) . ' ';
				}
				$assignedData[$i][$columnName] = trim($assignedData[$i][$columnName]);
				if (!empty($assignedData[$i][$columnName])) {
					$notEmptyCells++;
				}
			}
			// if there are less cells filled than required, don't import the address
			if ($notEmptyCells < $requiredNumberOfNotEmptyCells) {
				unset($assignedData[$i]);
			}
		}
		return $assignedData;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $addresses
	 * @param \Visol\Easyvote\Domain\Model\VotingDay $votingDay
	 * @return void
	 */
	public static function pushExcelExportFromAddresses(\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $addresses, \Visol\Easyvote\Domain\Model\VotingDay $votingDay) {

		// Create new PHPExcel object
		$objPHPExcel = new \PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("easyvote")
			->setLastModifiedBy("easyvote")
			->setTitle("easyvote Datenexport")
			->setSubject("easyvote Datenexport");

		$rowIndex = 1;

		// Add headers
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $rowIndex, 'Kundennummer')
			->setCellValue('B' . $rowIndex, 'Anrede')
			->setCellValue('C' . $rowIndex, 'Name')
			->setCellValue('D' . $rowIndex, 'Adresse')
			->setCellValue('E' . $rowIndex, 'Ort');
		$rowIndex++;

		// Add content
		foreach ($addresses as $address) {
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $rowIndex, $address->getCustomerNumber())
				->setCellValue('B' . $rowIndex, $address->getSalutation())
				->setCellValue('C' . $rowIndex, $address->getName())
				->setCellValue('D' . $rowIndex, $address->getStreet())
				->setCellValue('E' . $rowIndex, $address->getCity());
			$rowIndex++;
		}

		// Rename worksheet
		$currentDate = strftime('%d.%m.%y', $votingDay->getVotingDate()->getTimestamp());
		$objPHPExcel->getActiveSheet()->setTitle('easyvote ' . $currentDate);

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="easyvote' . $votingDay->getVotingDate()->getTimestamp() . '.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');

	}

}