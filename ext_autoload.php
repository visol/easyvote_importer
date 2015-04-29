<?php
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('easyvote_importer');
return array(
	'PHPExcel' => $extensionPath . 'Classes/Utility/PHPExcel.php',
	'PHPExcel_IOFactory' => $extensionPath . 'Classes/Utility/PHPExcel/IOFactory.php',
	'Dataset' => $extensionPath . 'Classes/Domain/Model/Dataset.php',
);
