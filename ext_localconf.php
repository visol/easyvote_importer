<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Datamanager',
	array(
		'DataManager' => 'index, logout, cityIndex, adminIndex, uploadList, remove, performUpload, assign, approve, remove, checkImport, performImport, newBlacklist, createBlacklist, listBlacklist, editBlacklist, updateBlacklist, deleteBlacklist, listExport, applyBlacklist, performExport, removeExport, reportExport, editBusinessUser, updateBusinessUser',
		
	),
	// non-cacheable actions
	array(
		'DataManager' => 'index, logout, cityIndex, adminIndex, uploadList, remove, performUpload, assign, approve, remove, checkImport, performImport, newBlacklist, createBlacklist, listBlacklist, editBlacklist, updateBlacklist, deleteBlacklist, listExport, applyBlacklist, performExport, removeExport, reportExport, editBusinessUser, updateBusinessUser',
	)
);

?>