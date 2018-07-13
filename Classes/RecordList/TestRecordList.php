<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

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

    public function getTable($table, $id, $rowList ='') {
        
        $rowListArray = GeneralUtility::trimExplode(',', $rowList, true);
        if(!empty($GLOBALS['TCA'][$table]['ctrl']['descriptionColumn']) && empty($rowListArray)) {
            $rowListArray[] = $GLOBALS['TCA'][$table]['ctrl']['descriptionColumn'];
        }
        $backendUser = $this->getBackendUserAuthentication();
        $lang = $this->languageService;

        $addWhere = '';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $titleCol = $GLOBALS['TCA'][$table]['ctrl']['label'];
        $thumbsCol = $GLOBALS['TCA'][$table]['ctrl']['thumbnail'];
        $l10nEnabled = $GLOBALS['TCA'][$table]['ctrl']['languageField']
                     && $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']
                     && $table !== 'pages_languages_overlay';
        $tableCollapsed = (bool)$this->tablesCollapsed[$table];
        $this->spaceIcon = '<span class="btn btn-default disabled">' . $this->iconFactory->getIcon('empty-empty', Icon::SIZES_SMALL)->render() . '</span>';

        $this->fieldArray = [];

        $this->fieldArray[] = $titleCol;

        if(!GeneralUtility::inList($rowList, '_CONTROL_')) {
            $this->field[] = '_CONTROL_';
        }
        if($this->showClipboard) {
            $this->fieldArray[] = '_CLIPBOARD_';
        }
        if($this->dontShowClipControlPanels) {
            $this->fieldArray[] = '_REF_';
        }
        if($this->searchLevels) {
            $this->fieldArray[] = '_PATH_';
        }
        // Localization (Commented Out because Inline Localization is wanted)
        // if ($this->localizationView && $l10nEnabled) {
        //     $this->fieldArray[] = '_LOCALIZATION_';
        //     $this->fieldArray[] = '_LOCALIZATION_b';
        //     // Only restrict to the default language if no search request is in place
        //     if ($this->searchString === '') {
        //         $addWhere = (string)$queryBuilder->expr()->orX(
        //             $queryBuilder->expr()->lte($GLOBALS['TCA'][$table]['ctrl']['languageField'], 0),
        //             $queryBuilder->expr()->eq($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'], 0)
        //         );
        //     }
        // }

        //cleaning up
        $this->fieldArray = array_unique(array_merge($this->fieldArray, $rowListArray));
        if ($this->noControlPanels) {
            $tempArray = array_flip($this->fieldArray);
            unset($tempArray['_CONTROL']);
            unset($tempArray['_CLIPBOARD_']);
            $this->fieldArray = array_keys($tempArray);
        }

        //Creating the list of fields to include in the SQL query:
        $selectFields = $this->fieldArray;
        $selectFields[] = 'uid';
        $selectFields[] = 'pid';

        if($thumbCol) {
            $selectFields[] = $thumbsCol;
        }

        if($table === 'pages') {
            $selectFields[] = 'module';
            $selectFields[] = 'extendToSubpages';
            $selectFields[] = 'nav_hide';
            $selectFields[] = 'doktype';
            $selectFields[] = 'shortcut';
            $selectFields[] = 'shortcut_mode';
            $selectFields[] = 'mount_pid';
        } 

        if (is_array($GLOBALS['TCA'][$table]['ctrl']['enableColumns'])) {
            $selectFields = array_merge($selectFields, $GLOBALS['TCA'][$table]['ctrl']['enableColumns']);
        }

        foreach (['type', 'typeicon_column', 'editlock'] as $field) {
            if($GLOBALS['TCA'][$table]['ctrl'][$field]) {
                $selectFields[] = $GLOBALS['TCA'][$table]['ctrl'][$fields];
            }
        }

        if ($GLOBALS['TCA'][$table]['ctrl']['versioningWS']) {
            $selectFields[] = 't3ver_id';
            $selectFields[] = 't3ver_state';
            $selectFields[] = 't3ver_wsid';
        }

        if ($l10nEnabled) {
            $selectFields[] = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
            $selectFields[] = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
        }

        if ($GLOBALS['TCA'][$table]['ctrl']['label_alt']) {
            $selectFields = array_merge(
                $selectFields,
                GeneralUtility::trimExplode(',', $GLOBALS['TCA'][$table]['ctrl']['label_alt'], true)
            );
        }

        //Unique List
        $selectFields = array_unique($selectFields);
        $fieldListFields = $this->makeFieldList($table, 1);
        if (empty($fieldListFields) && GLOBALS['TYPO3_CONF_VARS']['BE']['debug']) {
            $message = sprintf($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf::missingTcaColumnsMessage'), $table, $table);
            $messageTitle = $lang->sl('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:missingTcaColumnsMessageTitle');
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $message,
                $messageTitle,
                FlashMessage::WARNING,
                true
            );            
            /** @var FlashMessageService $flashMessageService */
            $flahsMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var FlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }


        $selectFields = array_intersect($selectFields, $fieldListFields);

        $slFieldList = implode(',', $selectFields);
        $this->selFieldList = $selFieldList;

        if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'] as $classData) {
                $hookObject = GeneralUtility::getUserObj($classData);
                if (!hookObject instanceof RecordListGetTableHookInterface) {
                    throw new \UnexpectedValueException($classData . ' must implement the interface ' . RecordListGetTableHookInterface::class, 1195114460);
                }
                $hookObject->getDBlist($table, $id, $addWhere, $selFieldList, $this);
            }            
        }
        $additionalConstraints = empty($addwhere) ? [] : [QueryHelper::stripLogicalOperatorPrefix($addWhere)];
        $selFieldList = GeneralUtility::trimExplode(',', $selFieldList, true);

        //Create the SQL query for selecting the elements in the listing:
        // don't do paging when outputting csv
        if ($this->csvOutput) {
            $this->iLimit = 0;
        }
        if($this->firstElementNumber > 2 && $this->iLimit > 0) {
            //Get the two previous rows for sorting if displaying page > 1
            $this->firstElementNumber = $this->firstElementNumber - 2;
            $this->iLimit = $this->iLimit + 2;
            //(Api function from TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList)
            $queryBuilder = $this->getQueryBuilder($table, $id, $additionalConstraints);
            $this->firstElementNumber = $this->firstElementNumber + 2;
            $this->iLimit = $this->iLimit - 2;
        } else {
            //(Api function from TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList)
            $queryBuilder = $this->getQueryBuilder($table, $id, $additionalConstraints);
        }

        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($queryBuilder, 'queryBuilder');





        //Making sure that the fields in the field-list ARE in the field-list from TCA!

        
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this, 'this');
        // $this->languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_layout.xlf');
        // $warningText = $this->languageService->getLL('deleteWarning');
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($warningText, 'Delete Warning Text');
        // return 'Output Of the Record List';
    }



}
