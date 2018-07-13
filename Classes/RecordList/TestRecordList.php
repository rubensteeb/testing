<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class TestRecordList extends DatabaseRecordList {


    public function getTable($tableName, $id, $fields ='') {
        \TYPO3\CMS\Core\Utiltiy\DebugUtility::debug($GLOBALS, 'GLOBALS', 'GLOBALS');
        return 'Output Of the Record List';
    }
}
