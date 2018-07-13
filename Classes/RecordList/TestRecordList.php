<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class TestRecordList extends DatabaseRecordList {


    public function getTable($tableName, $id, $fields ='') {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::debug($GLOBALS, 'GLOBALS');
        return 'Output Of the Record List';
    }
}
