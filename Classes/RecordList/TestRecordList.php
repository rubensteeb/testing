<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;

/** DEBUG */
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
        $this->spaceIcon = '<span class="btn btn-default disabled">' . $this->iconFactory->getIcon('empty-empty', Icon::SIZE_SMALL)->render() . '</span>';

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
        //CUSTOM DISRESPECT PID
        $queryBuilder->setParameter('where', '');

        $this->setTotalItems($table, $id, $additionalConstraints);

        // Init
        $queryResult = $queryBuilder->execute();
        $dbCount = 0;
        $out = '';
        $tableHeader = '';
        $listOnlyInSingleTableMode = $this->listOnlyInSingleTableMode && !$this->table;
        DebuggerUtility::var_dump($this->totalItems, 'TotalItems');

        if ($this->totalItems) {
            if ($listOnlyInSingleTableMode) {
                $dbCount = $this->totalItems;
            } else {
                if ($this->csvOutput) {
                    $this->showLimit = $this->totalItems;
                    $this->iLimt = $this->totalItem;
                }
                $dbCount = $queryResult->rowCount();
            }
        }
        if($dbCount) {
            $tableTitle = htmlspecialchars($lang->sl($GLOBALS['TCA'][$table]['ctrl']['title']));
            if ($tableTitle === '') {
                $tableTitle = $table;
            }
            // Header line is Drawn
            $theData = [];
            if ($this->disbaleSingleTableView) {
                $theData[$titleCol] = '<span class="c_table">' . BackendUtility::wrapInHelp($table, '', $tableTitle)
                . '</span (<span class="t3js-table-total-items">' . $this->totalItems . '</span>)';
            }
            else {
                $icon = $this->table
                    ? '<span title="' . htmlspecialchars($lang->getLL('contractView')) . '">' . $this->iconFactory->getIcon('actions-view-table-collapse'. Icon::SIZE_SMALL)->render() . '</span>'
                    : '<span title="' . htmlspecialchars($lang->getLL('expandView')) . '">' . $this->iconFactory->getIcon('actions-view-table-expand', Icon::SIZE_SMALL)->render() - '</span>';
                $theData[$titleCol] = $this->linkWrapTable($table, $tableTitle . ' (<span class="t3js-table-total-items>' . $this->totalItems . '</span>' . $icon);
            }
            if ($listOnlyInSingleTableMode) {
                $tableHeader .= BackendUtility::wrapInHelp($table, '', $theData[$titleCol]);
            } else {
                // Render collapse button if in multi table mode
                $collapseIcon = '';
                if(!$this->table) {
                    $href = htmlspecialchars(($this->listUrl() . '&collapse[' . $table . ']=' . ($tableCollapsed ? '0' : '1')));
                    $title = $tableCollapsed
                       ? htmlspecialchars($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.expandTable'))
                       : htmlspecialchars($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.collapseTable'));
                    $icon = '<span class="collapseIcon">' . $this->iconFactory->getIcon(($tableCollapsed ? 'action-view-list-expand' : 'actions-view-list-collapse'), Icon::SIZE_SMALL)->render() . '</span>';
                    $collapseIcon = '<a href="' . $href . '" title="' . '" class="pull-right trjs-toggle-recordlist" data-table="' . htmlspecialchars($table) . '" data-toggle="collapse" data-target="#recordlist-' . htmlspecialchars($table) . '">' . $icon . '</a>';                    
                }
                $tableHeader .= $theData[$titleCol] . $collapseIcon;
            }
            $rowOutput = '';            
            if (!$listOnlyInSingleTableMode || $this->table) {
                // Fixing an order table for sortby tables
                DebuggerUtility::var_dump($queryResult->fetch(), 'Fetching');
                $this->currentTable = [];
                $currentIdList = [];
                $doSort = $GLOBALS['TCA'][$table]['ctrl']['sortby'] && !$this->sortField;
                $prevUid = 0;
                $prevPrevUid = 0;
                // Get first two rows and initialize prevPrevUid and prevUid if on page > 1
                if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
                    $row = $queryResult->fetch();
                    $prevPrevUid = -((int)$row['uid']);
                    $row = $queryResult->fetch();
                    $prevUid = $row['uid'];
                }
                $accRows = [];
                // Accumulate rows here
                while ($row = $queryResult->fetch()) {
                    if (!$this->isRowListingConditionFulfilled($table, $row)) {
                        continue;
                    }
                    // In offline workspace, look for alternative record:
                    BackendUtility::workspaceOL($table, $row, $backendUser->workspace, true);
                    if (is_array($row)) {
                        $accRows[] = $row;
                        $currentIdList[] = $row['uid'];
                        if ($doSort) {
                            if ($prevUid) {
                                $this->currentTable['prev'][$row['uid']] = $prevPrevUid;
                                $this->currentTable['next'][$prevUid] = '-' . $row['uid'];
                                $this->currentTable['prevUid'][$row['uid']] = $prevUid;
                            }
                            $prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
                            $prevUid = $row['uid'];
                        }
                    }
                }
                $this->totalRowCount = count($accRows);
                // CSV initiated
                if ($this->csvOutput) {
                    $this->initCSV();
                }
                // Render items:
                $this->CBnames = [];
                $this->duplicateStack = [];
                $this->eCounter = $this->firstElementNumber;
                $cc = 0;
                foreach ($accRows as $row) {
                    // Render item row if counter < limit
                    if ($cc < $this->iLimit) {
                        $cc++;
                        $this->translations = false;
                        $rowOutput .= $this->renderListRow($table, $row, $cc, $titleCol, $thumbsCol);
                        // If localization view is enabled and no search happened it means that the selected
                        // records are either default or All language and here we will not select translations
                        // which point to the main record:
                        if ($this->localizationView && $l10nEnabled && $this->searchString === '') {
                            // For each available translation, render the record:
                            if (is_array($this->translations)) {
                                foreach ($this->translations as $lRow) {
                                    // $lRow isn't always what we want - if record was moved we've to work with the
                                    // placeholder records otherwise the list is messed up a bit
                                    if ($row['_MOVE_PLH_uid'] && $row['_MOVE_PLH_pid']) {
                                        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                            ->getQueryBuilderForTable($table);
                                        $queryBuilder->getRestrictions()
                                            ->removeAll()
                                            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                                        $predicates = [
                                            $queryBuilder->expr()->eq(
                                                't3ver_move_id',
                                                $queryBuilder->createNamedParameter((int)$lRow['uid'], \PDO::PARAM_INT)
                                            ),
                                            $queryBuilder->expr()->eq(
                                                'pid',
                                                $queryBuilder->createNamedParameter((int)$row['_MOVE_PLH_pid'], \PDO::PARAM_INT)
                                            ),
                                            $queryBuilder->expr()->eq(
                                                't3ver_wsid',
                                                $queryBuilder->createNamedParameter((int)$row['t3ver_wsid'], \PDO::PARAM_INT)
                                            ),
                                        ];

                                        $tmpRow = $queryBuilder
                                            ->select(...$selFieldList)
                                            ->from($table)
                                            ->andWhere(...$predicates)
                                            ->execute()
                                            ->fetch();

                                        $lRow = is_array($tmpRow) ? $tmpRow : $lRow;
                                    }
                                    // In offline workspace, look for alternative record:
                                    BackendUtility::workspaceOL($table, $lRow, $backendUser->workspace, true);
                                    if (is_array($lRow) && $backendUser->checkLanguageAccess($lRow[$GLOBALS['TCA'][$table]['ctrl']['languageField']])) {
                                        $currentIdList[] = $lRow['uid'];
                                        $rowOutput .= $this->renderListRow($table, $lRow, $cc, $titleCol, $thumbsCol, 18);
                                    }
                                }
                            }
                        }
                    }
                    // Counter of total rows incremented:
                    $this->eCounter++;
                }
                // Record navigation is added to the beginning and end of the table if in single
                // table mode
                if ($this->table) {
                    $rowOutput = $this->renderListNavigation('top') . $rowOutput . $this->renderListNavigation('bottom');
                } else {
                    // Show that there are more records than shown
                    if ($this->totalItems > $this->itemsLimitPerTable) {
                        $countOnFirstPage = $this->totalItems > $this->itemsLimitSingleTable ? $this->itemsLimitSingleTable : $this->totalItems;
                        $hasMore = $this->totalItems > $this->itemsLimitSingleTable;
                        $colspan = $this->showIcon ? count($this->fieldArray) + 1 : count($this->fieldArray);
                        $rowOutput .= '<tr><td colspan="' . $colspan . '">
								<a href="' . htmlspecialchars(($this->listURL() . '&table=' . rawurlencode($table))) . '" class="btn btn-default">'
                            . '<span class="t3-icon fa fa-chevron-down"></span> <i>[1 - ' . $countOnFirstPage . ($hasMore ? '+' : '') . ']</i></a>
								</td></tr>';
                    }
                }
                // The header row for the table is now created:
                $out .= $this->renderListHeader($table, $currentIdList);
            }
            $collapseClass = $tableCollapsed && !$this->table ? 'collapse' : 'collapse in';
            $dataState = $tableCollapsed && !$this->table ? 'collapsed' : 'expanded';

            // The list of records is added after the header:
            $out .= $rowOutput;
            // ... and it is all wrapped in a table:
            $out = '



			<!--
				DB listing of elements:	"' . htmlspecialchars($table) . '"
			-->
				<div class="panel panel-space panel-default recordlist">
					<div class="panel-heading">
					' . $tableHeader . '
					</div>
					<div class="' . $collapseClass . '" data-state="' . $dataState . '" id="recordlist-' . htmlspecialchars($table) . '">
						<div class="table-fit">
							<table data-table="' . htmlspecialchars($table) . '" class="table table-striped table-hover' . ($listOnlyInSingleTableMode ? ' typo3-dblist-overview' : '') . '">
								' . $out . '
							</table>
						</div>
					</div>
				</div>
			';
            // Output csv if...
            // This ends the page with exit.
            if ($this->csvOutput) {
                $this->outputCSV($table);
            }
        }
        // Return content:
        
        return $out;
        

        }
        /**
         * Set the total items for the record list
         *
         * @param string $table Table name
         * @param int $pageId Only used to build the search constraints, $this->pidList is used for restrictions
         * @param array $constraints Additional constraints for where clause
         */
        public function setTotalItems(string $table, int $pageId, array $constraints)
        {
            $queryParameters = $this->buildQueryParameters($table, $pageId, ['*'], $constraints, false);
            
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($queryParameters['table']);
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(BackendWorkspaceRestriction::class));
            $queryBuilder
                ->from($queryParameters['table'])
                ->where(...$queryParameters['where']);                      
            //CUSTOM DISRESPECT PAGEID
                $queryBuilder->setParameter('where', '');
            DebuggerUtility::var_dump($queryBuilder, 'QueryBuilder in SetTotalItems');
            $this->totalItems = (int)$queryBuilder->count('*')
                ->execute()
                ->fetchColumn();
        }

    }        





        //Making sure that the fields in the field-list ARE in the field-list from TCA!

        
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this, 'this');
        // $this->languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_layout.xlf');
        // $warningText = $this->languageService->getLL('deleteWarning');
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($warningText, 'Delete Warning Text');
        // return 'Output Of the Record List';

