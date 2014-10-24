<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Datamanager',
	'easyvote Data Manager'
);

/* Login-Panel */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Businessuser',
	'easyvote Data Manager: Login-Panel',
	'EXT:easyvote_importer/ext_icon.gif'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'easyvote Importer');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_easyvoteimporter_domain_model_dataset', 'EXT:easyvote_importer/Resources/Private/Language/locallang_csh_tx_easyvoteimporter_domain_model_dataset.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_dataset');
$TCA['tx_easyvoteimporter_domain_model_dataset'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_dataset',
		'label' => 'file',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'origUid' => 't3_origuid',
		//'languageField' => 'sys_language_uid',
		//'transOrigPointerField' => 'l10n_parent',
		//'transOrigDiffSourceField' => 'l10n_diffsource',

		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'file,voting_day,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Dataset.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_dataset.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_easyvoteimporter_domain_model_address', 'EXT:easyvote_importer/Resources/Private/Language/locallang_csh_tx_easyvoteimporter_domain_model_address.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_address');
$TCA['tx_easyvoteimporter_domain_model_address'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_address',
		'label' => 'name',
		'label_alt' => 'city, blacklisted',
		'label_alt_force' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'origUid' => 't3_origuid',
		//'languageField' => 'sys_language_uid',
		//'transOrigPointerField' => 'l10n_parent',
		//'transOrigDiffSourceField' => 'l10n_diffsource',

		'enablecolumns' => array(
		),
		//'searchFields' => '',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Address.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_address.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_easyvoteimporter_domain_model_blacklist', 'EXT:easyvote_importer/Resources/Private/Language/locallang_csh_tx_easyvoteimporter_domain_model_blacklist.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_blacklist');
$TCA['tx_easyvoteimporter_domain_model_blacklist'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_blacklist',
		'label' => 'last_name',
		'label_alt' => 'first_name, street, zip',
		'label_alt_force' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'origUid' => 't3_origuid',
		//'languageField' => 'sys_language_uid',
		//'transOrigPointerField' => 'l10n_parent',
		//'transOrigDiffSourceField' => 'l10n_diffsource',

		'enablecolumns' => array(
		),
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Blacklist.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_blacklist.gif'
	),
);

if (!isset($TCA['fe_users']['ctrl']['type'])) {
	// no type field defined, so we define it here. This will only happen the first time the extension is installed!!
	$TCA['fe_users']['ctrl']['type'] = 'tx_extbase_type';
	$tempColumns = array();
	$tempColumns[$TCA['fe_users']['ctrl']['type']] = array(
		'exclude' => 1,
		'label'   => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.tx_extbase_type',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.tx_extbase_type.0','0'),
			),
			'size' => 1,
			'maxitems' => 1,
			'default' => 'Tx_EasyvoteImporter_BusinessUser'
		)
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
}

$TCA['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] = $TCA['fe_users']['types']['Tx_Extbase_Domain_Model_FrontendUser']['showitem'];
$TCA['fe_users']['columns'][$TCA['fe_users']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser','Tx_EasyvoteImporter_BusinessUser');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', $TCA['fe_users']['ctrl']['type'],'','after:hidden');


$tmp_easyvote_importer_columns = array(
	'gender' => array (
		'exclude' => 1,
		'label'  => 'LLL:EXT:easyvote/Resources/Private/Language/locallang_db.xlf:tx_easyvote_domain_model_communityuser.gender',
		'config' => array (
			'type'    => 'radio',
			'default' => 2,
			'items'   => array(
				array('LLL:EXT:easyvote/Resources/Private/Language/locallang_db.xlf:tx_easyvote_domain_model_communityuser.gender.m', 1),
				array('LLL:EXT:easyvote/Resources/Private/Language/locallang_db.xlf:tx_easyvote_domain_model_communityuser.gender.f', 2)
			)
		)
	),
	'customer_number' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.customer_number',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim,required'
		),
	),
	'user_language' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.user_language',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.user_language.german', 1),
				array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.user_language.french', 2),
				array('LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser.user_language.italian', 3),
			),
		),
	),
	'kanton' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:easyvote/Resources/Private/Language/locallang_db.xlf:tx_easyvote_domain_model_metavotingproposal.kanton',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('Kanton wählen...', 0)
			),
			'foreign_table' => 'tx_easyvote_domain_model_kanton',
			'foreign_table_where' => 'ORDER BY tx_easyvote_domain_model_kanton.name',
			'minitems' => 1,
			'maxitems' => 1,
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
			'maxitems'      => 9999,
			'appearance' => array(
				'collapseAll' => 0,
				'levelLinksPosition' => 'top',
				'showSynchronizationLink' => 1,
				'showPossibleLocalizationRecords' => 1,
				'showAllLocalizationLink' => 1
			),
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tmp_easyvote_importer_columns);


$TCA['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] = $TCA['fe_users']['types']['Tx_Extbase_Domain_Model_FrontendUser']['showitem'];
$TCA['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] .= ',--div--;LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_businessuser,';
$TCA['fe_users']['types']['Tx_EasyvoteImporter_BusinessUser']['showitem'] .= 'customer_number, user_language, kanton, target_group_start, target_group_end, datasets';

?>