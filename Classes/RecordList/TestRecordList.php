<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\RecordList\DatabaseRecordList;

class TestRecordList extends DatabaseRecordList {


    public function getTable($tableName, $id, $fields ='') {
        return 'Output Of the Record List';
    }
}
