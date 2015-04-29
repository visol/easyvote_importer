<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_easyvoteimporter_domain_model_dataset'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_dataset',
		'label' => 'voting_day',
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
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('easyvote_importer') . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_dataset.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, file, source_dataset, voting_day',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, crdate, file, source_dataset, processed, firstrow_columnnames, column_configuration, imported_addresses, voting_day, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
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
			'label' => 'Upload-Datum',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'date',
				'readOnly' => 1
			),
		),
		'processed' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'Daten importiert am',
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
		'file' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_dataset.file',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'uploadfolder' => 'uploads/tx_easyvoteimporter',
				'allowed' => 'xls,xlsx,csv',
				'disallowed' => 'php',
				'size' => 1,
				'readOnly' => 1
			),
		),
		'firstrow_columnnames' => array(
			'exclude' => 1,
			'label' => 'Erste Zeile hat Spaltennamen',
			'config' => array(
				'type' => 'check',
				'default' => 0,
				'readOnly' => 1
			),
		),
		'column_configuration' => array(
			'exclude' => 1,
			'label' => 'Spaltenzuordnung',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 5,
				'readOnly' => 1
			),
		),
		'imported_addresses' => array(
			'exclude' => 1,
			'label' => 'Anzahl importierter Adressen',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1
			),
		),
		'voting_day' => array(
			'exclude' => 0,
			'label' => 'Abstimmungstag',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_easyvote_domain_model_votingday',
				'minitems' => 1,
				'maxitems' => 1,
				'readOnly' => 1,
				'items' => array(
					array('', ''),
				),
			),
		),
		'source_dataset' => array(
			'exclude' => 0,
			'label' => 'Daten importiert von Dataset',
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
		'businessuser' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
);
