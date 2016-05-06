<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_easyvoteimporter_domain_model_blacklist'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:easyvote_importer/Resources/Private/Language/locallang_db.xlf:tx_easyvoteimporter_domain_model_blacklist',
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

		'enablecolumns' => array(),
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('easyvote_importer') . 'Resources/Public/Icons/tx_easyvoteimporter_domain_model_blacklist.gif'
	),
	'types' => array(
		'1' => array('showitem' => 'first_name, last_name, street, zip_code, reason, comment, crdate'),
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
			'label' => 'Hinzugefügt am',
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
		'first_name' => array(
			'exclude' => 1,
			'label' => 'Vorname',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			),
		),
		'last_name' => array(
			'exclude' => 1,
			'label' => 'Name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			),
		),
		'street' => array(
			'exclude' => 1,
			'label' => 'Adresse (muss genau übereinstimmen!)',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			),
		),
		'zip_code' => array(
			'exclude' => 1,
			'label' => 'PLZ',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			),
		),
        'reason' => array(
            'exclude' => 1,
            'label' => 'Grund',
            'config' => array(
                'type' => 'select',
                'minitems' => 0,
                'maxitems' => 1,
                'items' => array(
                    array('', 0),
                    array('Kein Interesse an Politik', 1),
                    array('Ich benötige keine einfachen und neutralen Abstimmungsinformationen', 2),
                    array('Anderer (Grund bitte unter Bemerkung angeben)', 3),
                ),
            ),
        ),
        'comment' => array(
            'exclude' => 1,
            'label' => 'Bemerkung',
            'config' => array(
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
            ),
        ),
	),
);
