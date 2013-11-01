<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Datamanager',
	array(
		'DataManager' => 'index, cityIndex, adminIndex, uploadList, remove, performUpload, assign, approve, remove, checkImport, performImport',
		
	),
	// non-cacheable actions
	array(
		'DataManager' => 'index, cityIndex, adminIndex, uploadList, remove, performUpload, assign, approve, remove, checkImport, performImport',
		
	)
);

?>