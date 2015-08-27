<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'vendor/autoload.php';
require_once($composerAutoloadFile);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Datamanager',
	'easyvote Data Manager'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_datamanager';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/flexform_datamanager.xml');

/* Login-Panel */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Businessuser',
	'easyvote Data Manager: Login-Panel',
	'EXT:easyvote_importer/ext_icon.gif'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'easyvote Importer');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_dataset');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_address');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyvoteimporter_domain_model_blacklist');

