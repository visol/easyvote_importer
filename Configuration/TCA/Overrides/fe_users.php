<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
	// no type field defined, so we define it here. This will only happen the first time the extension is installed!!
	$GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
	$tempColumns = array();
	$tempColumns[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.tx_extbase_type',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.tx_extbase_type.0', '0'),
			),
			'size' => 1,
			'maxitems' => 1,
			'default' => 'Tx_EasyvoteImporter_BusinessUser'
		)
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
}

$GLOBALS['TCA']['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['Tx_Extbase_Domain_Model_FrontendUser']['showitem'];
$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser', 'Tx_EasyvoteImporter_BusinessUser');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', $GLOBALS['TCA']['fe_users']['ctrl']['type'], '', 'after:hidden');


$tmp_easyvote_importer_columns = array(
	'customer_number' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.customer_number',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim,required'
		),
	),
	'target_group_start' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.target_group_start',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
	'target_group_end' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.target_group_end',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
	'datasets' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.datasets',
		'config' => array(
			'type' => 'inline',
			'foreign_table' => 'tx_easyvoteimporter_domain_model_dataset',
			'foreign_field' => 'businessuser',
			'maxitems' => 9999,
			'appearance' => array(
				'collapseAll' => 1,
				'levelLinksPosition' => 'top',
			),
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_easyvote_importer_columns);

$GLOBALS['TCA']['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['Tx_Extbase_Domain_Model_FrontendUser']['showitem'];
$GLOBALS['TCA']['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] .= ',--div--;LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser,';
$GLOBALS['TCA']['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] .= 'customer_number, user_language, kanton, target_group_start, target_group_end, datasets';
