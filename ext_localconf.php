<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'vendor/autoload.php';
require_once($composerAutoloadFile);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Datamanager',
	array(
		'DataManager' => 'index, logout, cityIndex, adminIndex, uploadList, remove, performUpload, assign, removeDatasetAndAddresses, approve, remove, checkImport, performImport, newBlacklist, createBlacklist, listBlacklist, editBlacklist, updateBlacklist, deleteBlacklist, listExport, applyBlacklist, performExport, removeExport, reportExport, editBusinessUser, updateBusinessUser, listStats, copyAddressesFromOtherVotingDay',
		
	),
	// non-cacheable actions
	array(
		'DataManager' => 'index, logout, cityIndex, adminIndex, uploadList, remove, performUpload, assign, removeDatasetAndAddresses, approve, remove, checkImport, performImport, newBlacklist, createBlacklist, listBlacklist, editBlacklist, updateBlacklist, deleteBlacklist, listExport, applyBlacklist, performExport, removeExport, reportExport, editBusinessUser, updateBusinessUser, listStats, copyAddressesFromOtherVotingDay',
	)
);

/* Login-Panel */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Businessuser',
	array(
		'BusinessUser' => 'loginPanel',
	),
	// non-cacheable actions
	array(
		'BusinessUser' => 'loginPanel',
	)
);
