<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class TestRecordList extends DatabaseRecordList {


    public function __construct() {
        parent::__construct();
        $this->getLanguageService()->includeLLFile('EXT:backend/Resources/Private/Language/locallang_layout.xlf');
    }

    public function getTable($tableName, $id, $fields ='') {
        $warningText = $this->getLanguageService()->getLL('DeleteWarning');
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($warningText, 'Delete Warning Text');
        return 'Output Of the Record List';
    }



}
