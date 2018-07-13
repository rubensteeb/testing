<?php
defined('TYPO3_MODE') || die('Access denied');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTS/Mod/WebLayout/BackendLayouts.ts">');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	$_EXTKEY,
	'setup',
	'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/TypoScript/setup.ts">'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'RubenSteeb.TestModelRelations',
	'TestPlugin',
	[
		'SuperClass' => 'list',
	],
	[
		'SuperClass' => 'list',
	]
);
