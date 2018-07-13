<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class TestRecordList extends DatabaseRecordList {


    public function getTable($tableName, $id, $fields ='') {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->getLanguageService(), 'LanguageService');
        return 'Output Of the Record List';
    }



}
