<?php
defined('TYPO3_MODE') or die();

if(TYPO3_MODE == 'BE') {
    if(!isset($TBE_MODULES['TestingMainmodule'])) {
        $temp_TBE_MODULES = array();
		foreach ($TBE_MODULES as $key => $val) {
			if ($key == 'web') {
				$temp_TBE_MODULES[$key] = $val;
				$temp_TBE_MODULES['TestingMainmodule'] = '';
			} else {
				$temp_TBE_MODULES[$key] = $val;
			}
		}
        $TBE_MODULES = $temp_TBE_MODULES; 
    }
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'RubenSteeb.Testing',
        'mainModule',
        '',
        '',
        array(),
        array(
            'access' => 'user, group',
            'icon' => 'EXT:form/Resources/Public/Icons/module-form.svg',
            'labels' => 'LLL:EXT:' . $_EXTKEY . 'Resources/Private/Language/locallang_main_module.xlf',
        )
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'RubenSteeb.Testing',
        'mainModule',
        'forms',
        'top',
        [
            'Main' => 'index, show, create, delete'
        ],
        [
            'access' => 'user, group',
            'icon' => 'EXT:form/Resources/Public/Icons/module-form.svg',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_submodule_main.xlf'
        ]
    );

}
