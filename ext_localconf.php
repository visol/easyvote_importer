<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Datauploader',
	array(
		'DataUploader' => 'index, upload, assign, approve, remove',
		
	),
	// non-cacheable actions
	array(
		'DataUploader' => 'index, upload, assign, approve, remove',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Datamanager',
	array(
		'DataManager' => 'index, edit, approve, download, dropData',
		
	),
	// non-cacheable actions
	array(
		'DataManager' => 'index, edit, approve, download, dropData',
		
	)
);

?>