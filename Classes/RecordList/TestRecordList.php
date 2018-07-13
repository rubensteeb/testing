<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Lang\LanguageService;


class TestRecordList extends DatabaseRecordList {


    /**
     * @var LanguageService;
     */
    protected $languageService;

    /**
     * @param LanguageService $languageService
     */
    public function injectLanguageService(LanguageService $languageService) {
        $this->languageService = $languageService;
    }

    public function __construct() {
        parent::__construct();
        
    }

    public function getTable($tableName, $id, $fields ='') {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this, 'this');
        $this->languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_layout.xlf');
        $warningText = $this->languageService->getLL('deleteWarning');
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($warningText, 'Delete Warning Text');
        return 'Output Of the Record List';
    }



}
