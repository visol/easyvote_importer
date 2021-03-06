<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_easyvoteimporter_domain_model_address'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_address',
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

		'enablecolumns' => array(),
		//'searchFields' => '',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('easyvote_importer') . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_address.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'customer_number, businessuser, blacklisted, salutation, name, street, city, import_file_name, crdate, dataset, voting_day',
	),
	'types' => array(
		'1' => array('showitem' => 'customer_number, businessuser, blacklisted, salutation, name, street, city, import_file_name, crdate, dataset, voting_day'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_easyvoteimporter_domain_model_dataset',
				'foreign_table_where' => 'AND tx_easyvoteimporter_domain_model_dataset.pid=###CURRENT_PID### AND tx_easyvoteimporter_domain_model_dataset.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'crdate' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'Import-Datum',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'date',
				'readOnly' => 1
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'import_file_name' => array(
			'exclude' => 1,
			'label' => 'Dateiname der Quelle',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'blacklisted' => array(
			'exclude' => 1,
			'label' => 'Adresse ist in der Blacklist',
			'config' => array(
				'type' => 'check',
				'readOnly' => 1,
			),
		),
		'customer_number' => array(
			'exclude' => 1,
			'label' => 'Kundennummer Gemeinde',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'businessuser' => array(
			'exclude' => 1,
			'label' => 'Gemeinde',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'minitems' => 1,
				'maxitems' => 1,
				'readOnly' => 1,
				'items' => array(
					array('', ''),
				),
			),
		),
		'salutation' => array(
			'exclude' => 1,
			'label' => 'Anrede',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'name' => array(
			'exclude' => 1,
			'label' => 'Vorname und Name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'street' => array(
			'exclude' => 1,
			'label' => 'Adresse',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'city' => array(
			'exclude' => 1,
			'label' => 'PLZ und Ort',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'readOnly' => 1,
			),
		),
		'dataset' => array(
			'exclude' => 0,
			'label' => 'Dataset',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_easyvoteimporter_domain_model_dataset',
				'minitems' => 0,
				'maxitems' => 1,
				'readOnly' => 1,
				'items' => array(
					array('', ''),
				),
			),
		),
		'voting_day' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_dataset.voting_day',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_easyvote_domain_model_votingday',
				'minitems' => 1,
				'maxitems' => 1,
				'readOnly' => 1
			),
		),
	),
);
