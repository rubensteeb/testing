<?php
namespace RubenSteeb\Testing\RecordList;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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

    /**
     * Creates the listing of records from a single table
     *
     * @param string $table Table name
     * @param int $id Page id
     * @param string $rowList List of fields to show in the listing. Pseudo fields will be added including the record header.
     * @throws \UnexpectedValueException
     * @return string HTML table with the listing for the record.
     */
    public function getTable($table, $id, $rowList = '')
    {
        $rowListArray = GeneralUtility::trimExplode(',', $rowList, true);
        // if no columns have been specified, show description (if configured)
        if (!empty($GLOBALS['TCA'][$table]['ctrl']['descriptionColumn']) && empty($rowListArray)) {
            $rowListArray[] = $GLOBALS['TCA'][$table]['ctrl']['descriptionColumn'];
        }
        $backendUser = $this->getBackendUserAuthentication();
        $lang = $this->getLanguageService();
        // Init
        $addWhere = '';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $titleCol = $GLOBALS['TCA'][$table]['ctrl']['label'];
        $thumbsCol = $GLOBALS['TCA'][$table]['ctrl']['thumbnail'];
        $l10nEnabled = $GLOBALS['TCA'][$table]['ctrl']['languageField']
                     && $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']
                     && $table !== 'pages_language_overlay';
        $tableCollapsed = (bool)$this->tablesCollapsed[$table];
        // prepare space icon
        $this->spaceIcon = '<span class="btn btn-default disabled">' . $this->iconFactory->getIcon('empty-empty', Icon::SIZE_SMALL)->render() . '</span>';
        // Cleaning rowlist for duplicates and place the $titleCol as the first column always!
        $this->fieldArray = [];
        // title Column
        // Add title column
        $this->fieldArray[] = $titleCol;
        // Control-Panel
        if (!GeneralUtility::inList($rowList, '_CONTROL_')) {
            $this->fieldArray[] = '_CONTROL_';
        }
        // Clipboard
        if ($this->showClipboard) {
            $this->fieldArray[] = '_CLIPBOARD_';
        }
        // Ref
        if (!$this->dontShowClipControlPanels) {
            $this->fieldArray[] = '_REF_';
        }
        // Path
        if ($this->searchLevels) {
            $this->fieldArray[] = '_PATH_';
        }
        // Localization
        if ($this->localizationView && $l10nEnabled) {
            $this->fieldArray[] = '_LOCALIZATION_';
            $this->fieldArray[] = '_LOCALIZATION_b';
            // Only restrict to the default language if no search request is in place
            if ($this->searchString === '') {
                $addWhere = (string)$queryBuilder->expr()->orX(
                    $queryBuilder->expr()->lte($GLOBALS['TCA'][$table]['ctrl']['languageField'], 0),
                    $queryBuilder->expr()->eq($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'], 0)
                );
            }
        }
        // Cleaning up:
        $this->fieldArray = array_unique(array_merge($this->fieldArray, $rowListArray));
        if ($this->noControlPanels) {
            $tempArray = array_flip($this->fieldArray);
            unset($tempArray['_CONTROL_']);
            unset($tempArray['_CLIPBOARD_']);
            $this->fieldArray = array_keys($tempArray);
        }
        // Creating the list of fields to include in the SQL query:
        $selectFields = $this->fieldArray;
        $selectFields[] = 'uid';
        $selectFields[] = 'pid';
        // adding column for thumbnails
        if ($thumbsCol) {
            $selectFields[] = $thumbsCol;
        }
        if ($table === 'pages') {
            $selectFields[] = 'module';
            $selectFields[] = 'extendToSubpages';
            $selectFields[] = 'nav_hide';
            $selectFields[] = 'doktype';
            $selectFields[] = 'shortcut';
            $selectFields[] = 'shortcut_mode';
            $selectFields[] = 'mount_pid';
        }
        if (is_array($GLOBALS['TCA'][$table]['ctrl']['enablecolumns'])) {
            $selectFields = array_merge($selectFields, $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']);
        }
        foreach (['type', 'typeicon_column', 'editlock'] as $field) {
            if ($GLOBALS['TCA'][$table]['ctrl'][$field]) {
                $selectFields[] = $GLOBALS['TCA'][$table]['ctrl'][$field];
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
        // Unique list!
        $selectFields = array_unique($selectFields);
        $fieldListFields = $this->makeFieldList($table, 1);
        if (empty($fieldListFields) && $GLOBALS['TYPO3_CONF_VARS']['BE']['debug']) {
            $message = sprintf($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:missingTcaColumnsMessage'), $table, $table);
            $messageTitle = $lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_mod_web_list.xlf:missingTcaColumnsMessageTitle');
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $message,
                $messageTitle,
                FlashMessage::WARNING,
                true
            );
            /** @var $flashMessageService FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }
        // Making sure that the fields in the field-list ARE in the field-list from TCA!
        $selectFields = array_intersect($selectFields, $fieldListFields);
        // Implode it into a list of fields for the SQL-statement.
        $selFieldList = implode(',', $selectFields);
        $this->selFieldList = $selFieldList;
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'] as $classData) {
                $hookObject = GeneralUtility::getUserObj($classData);
                if (!$hookObject instanceof RecordListGetTableHookInterface) {
                    throw new \UnexpectedValueException($classData . ' must implement interface ' . RecordListGetTableHookInterface::class, 1195114460);
                }
                $hookObject->getDBlistQuery($table, $id, $addWhere, $selFieldList, $this);
            }
        }
        $additionalConstraints = empty($addWhere) ? [] : [QueryHelper::stripLogicalOperatorPrefix($addWhere)];
        $selFieldList = GeneralUtility::trimExplode(',', $selFieldList, true);

        // Create the SQL query for selecting the elements in the listing:
        // do not do paging when outputting as CSV
        if ($this->csvOutput) {
            $this->iLimit = 0;
        }
        if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
            // Get the two previous rows for sorting if displaying page > 1
            $this->firstElementNumber = $this->firstElementNumber - 2;
            $this->iLimit = $this->iLimit + 2;
            // (API function from TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList)
            $queryBuilder = $this->getQueryBuilder($table, $id, $additionalConstraints);
            $this->firstElementNumber = $this->firstElementNumber + 2;
            $this->iLimit = $this->iLimit - 2;
        } else {
            // (API function from TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList)
            $queryBuilder = $this->getQueryBuilder($table, $id, $additionalConstraints);
        }

        // Finding the total amount of records on the page
        // (API function from TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList)
        $this->setTotalItems($table, $id, $additionalConstraints);
        
        // Init:
        $queryResult = $queryBuilder->execute();
        $dbCount = 0;
        $out = '';
        $tableHeader = '';
        $listOnlyInSingleTableMode = $this->listOnlyInSingleTableMode && !$this->table;
        // If the count query returned any number of records, we perform the real query,
        // selecting records.
        if ($this->totalItems) {
            // Fetch records only if not in single table mode
            if ($listOnlyInSingleTableMode) {
                $dbCount = $this->totalItems;
            } else {
                // Set the showLimit to the number of records when outputting as CSV
                if ($this->csvOutput) {
                    $this->showLimit = $this->totalItems;
                    $this->iLimit = $this->totalItems;
                }
                $dbCount = $queryResult->rowCount();
            }
        }
        // If any records was selected, render the list:
        if ($dbCount) {
            $tableTitle = htmlspecialchars($lang->sL($GLOBALS['TCA'][$table]['ctrl']['title']));
            if ($tableTitle === '') {
                $tableTitle = $table;
            }
            // Header line is drawn
            $theData = [];
            if ($this->disableSingleTableView) {
                $theData[$titleCol] = '<span class="c-table">' . BackendUtility::wrapInHelp($table, '', $tableTitle)
                    . '</span> (<span class="t3js-table-total-items">' . $this->totalItems . '</span>)';
            } else {
                $icon = $this->table
                    ? '<span title="' . htmlspecialchars($lang->getLL('contractView')) . '">' . $this->iconFactory->getIcon('actions-view-table-collapse', Icon::SIZE_SMALL)->render() . '</span>'
                    : '<span title="' . htmlspecialchars($lang->getLL('expandView')) . '">' . $this->iconFactory->getIcon('actions-view-table-expand', Icon::SIZE_SMALL)->render() . '</span>';
                $theData[$titleCol] = $this->linkWrapTable($table, $tableTitle . ' (<span class="t3js-table-total-items">' . $this->totalItems . '</span>) ' . $icon);
            }
            if ($listOnlyInSingleTableMode) {
                $tableHeader .= BackendUtility::wrapInHelp($table, '', $theData[$titleCol]);
            } else {
                // Render collapse button if in multi table mode
                $collapseIcon = '';
                if (!$this->table) {
                    $href = htmlspecialchars(($this->listURL() . '&collapse[' . $table . ']=' . ($tableCollapsed ? '0' : '1')));
                    $title = $tableCollapsed
                        ? htmlspecialchars($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.expandTable'))
                        : htmlspecialchars($lang->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:labels.collapseTable'));
                    $icon = '<span class="collapseIcon">' . $this->iconFactory->getIcon(($tableCollapsed ? 'actions-view-list-expand' : 'actions-view-list-collapse'), Icon::SIZE_SMALL)->render() . '</span>';
                    $collapseIcon = '<a href="' . $href . '" title="' . $title . '" class="pull-right t3js-toggle-recordlist" data-table="' . htmlspecialchars($table) . '" data-toggle="collapse" data-target="#recordlist-' . htmlspecialchars($table) . '">' . $icon . '</a>';
                }
                $tableHeader .= $theData[$titleCol] . $collapseIcon;
            }
            // Render table rows only if in multi table view or if in single table view
           $rowOutpu = '';
           $accRows = [];

           $this->currentTable = [];
           $currentIdList = [];
           $doSort = $GLOBALS['TCA'][$table]['ctrl']['sortby'] && !$this->sortField;
           $prevUid = 0;
           $prevPrevUid = 0;

           while ($row = $queryResult->fetch()) {
               if (!$this->isRowListingConditionFulfilled($table, $row)) {
                   continue;
               }
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

           //CSV inititaded
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
                    

                }
                $this->eCounter++;
           }

           if ($this->table) {
               $rowOutput = $this->renderListNavigation('top') . $rowOutput . $this->renderListNavigation('bottom');
           } else {
               // Show that there are more records than shown
               if ($this->totalItems > $this->itemsLimitPerTable) {
                   $countOnFirstPage = $this->totaItems > $this->itemsLimitSingleTable ? $this->itemsLimitSingleTable : $this->totalItems;
                   $hasMore =  $this->totatItems > $this->itemsLimitTable;
                   $colspan = $this->showIcon ? count($this->fieldArray) + 1 : count($this->fieldArray);
                   $rowOutput .= '<tr><td colspan="' . $colspan . '">
								<a href="' . htmlspecialchars(($this->listURL() . '&table=' . rawurlencode($table))) . '" class="btn btn-default">'
                            . '<span class="t3-icon fa fa-chevron-down"></span> <i>[1 - ' . $countOnFirstPage . ($hasMore ? '+' : '') . ']</i></a>
								</td></tr>';
                    
               }
           }
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
        
        // Return content:
            return $out;
        }

        /**
         * Rendering a single row for the list
         * @param string $table Table name
         * @param mixed[] $row Current record
         * @param int $cc Counter, counting for each time an element is renderd (used for alternating colors)
         * @param string $titleCol Table field (column) where header value is found
         * @param string $thumbsCol Table Field (column) where (possible) thumbnails can be found
         * @param int $indent Indent from the left.
         * @return string Table row for the element
         * @access private
         * @see getTable()
         */
        public function renderListRow($table, $row, $cc, $titleCol, $thumbsCol, $indent = 0)
        {
            if (!is_array($row)) {
                return '';
            }            
            DebuggerUtility::var_dump($row, 'Row in Render List Row');

            $rowOutput = '';
            $id_orig = null;
            // If in search mode, make sure the preview will show the correct page
            if ((string)$this->searchString !== '') {
                $id_orig = $this->id;
                $this->id = $row['pid'];
            }
    
            $tagAttributes = [
                'class' => ['t3js-entity'],
                'data-table' => $table,
                'title' => 'id=' . $row['uid'],
            ];
    
            // Add special classes for first and last row
            if ($cc == 1 && $indent == 0) {
                $tagAttributes['class'][] = 'firstcol';
            }
            if ($cc == $this->totalRowCount || $cc == $this->iLimit) {
                $tagAttributes['class'][] = 'lastcol';
            }
            // Overriding with versions background color if any:
            if (!empty($row['_CSSCLASS'])) {
                $tagAttributes['class'] = [$row['_CSSCLASS']];
            }
            // Incr. counter.
            $this->counter++;
            // The icon with link
            $toolTip = BackendUtility::getRecordToolTip($row, $table);

            DebuggerUtility::var_dump($this->fieldArray, 'fieldArray');

            $additionalStyle = $indent ? ' style="margin-left: ' . $indent . 'px;"' : '';
            $iconImg = '<span ' . $toolTip . ' ' . $additionalStyle . '>'
                . $this->iconFactory->getIconForRecord($table, $row, Icon::SIZE_SMALL)->render()
                . '</span>';            
            $theIcon = $this->clickMenuEnabled ? BackendUtility::wrapClickMenuOnIcon($iconImg, $table, $row['uid']) : $iconImg;
            // Preparing and getting the data-array
            $theData = [];
            $localizationMarkerClass = '';
            foreach ($this->fieldArray as $fCol) {
                
                if ($fCol == $titleCol) {
                    $recTitle = BackendUtility::getRecordTitle($table, $row, false, true);
                    $warning = '';
                    // If the record is edit-locked	by another user, we will show a little warning sign:
                    $lockInfo = BackendUtility::isRecordLocked($table, $row['uid']);
                    if ($lockInfo) {
                        $warning = '<span data-toggle="tooltip" data-placement="right" data-title="' . htmlspecialchars($lockInfo['msg']) . '">'
                            . $this->iconFactory->getIcon('status-warning-in-use', Icon::SIZE_SMALL)->render() . '</span>';
                    }
                    $theData[$fCol] = $theData['__label'] = $warning . $this->linkWrapItems($table, $row['uid'], $recTitle, $row);
                    // Render thumbnails, if:
                    // - a thumbnail column exists
                    // - there is content in it
                    // - the thumbnail column is visible for the current type
                    $type = 0;
                    if (isset($GLOBALS['TCA'][$table]['ctrl']['type'])) {
                        $typeColumn = $GLOBALS['TCA'][$table]['ctrl']['type'];
                        $type = $row[$typeColumn];
                    }
                    // If current type doesn't exist, set it to 0 (or to 1 for historical reasons,
                    // if 0 doesn't exist)
                    if (!isset($GLOBALS['TCA'][$table]['types'][$type])) {
                        $type = isset($GLOBALS['TCA'][$table]['types'][0]) ? 0 : 1;
                    }
                    $visibleColumns = $GLOBALS['TCA'][$table]['types'][$type]['showitem'];
    
                    if ($this->thumbs &&
                        trim($row[$thumbsCol]) &&
                        preg_match('/(^|(.*(;|,)?))' . $thumbsCol . '(((;|,).*)|$)/', $visibleColumns) === 1
                    ) {
                        $thumbCode = '<br />' . $this->thumbCode($row, $table, $thumbsCol);
                        $theData[$fCol] .= $thumbCode;
                        $theData['__label'] .= $thumbCode;
                    }
                    if (isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                        && $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] != 0
                        && $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] != 0
                    ) {
                        // It's a translated record with a language parent
                        $localizationMarkerClass = ' localization';
                    }
                } elseif ($fCol === 'pid') {
                    $theData[$fCol] = $row[$fCol];
                } elseif ($fCol === '_PATH_') {
                    $theData[$fCol] = $this->recPath($row['pid']);
                } elseif ($fCol === '_REF_') {
                    $theData[$fCol] = $this->createReferenceHtml($table, $row['uid']);
                } elseif ($fCol === '_CONTROL_') {
                    $theData[$fCol] = $this->makeControl($table, $row);
                } elseif ($fCol === '_CLIPBOARD_') {
                    $theData[$fCol] = $this->makeClip($table, $row);
                } elseif ($fCol === '_LOCALIZATION_') {
                    list($lC1, $lC2) = $this->makeLocalizationPanel($table, $row);
                    $theData[$fCol] = $lC1;
                    $theData[$fCol . 'b'] = '<div class="btn-group">' . $lC2 . '</div>';
                } elseif ($fCol === '_LOCALIZATION_b') {
                    // deliberately empty
                } else {
                    $pageId = $table === 'pages' ? $row['uid'] : $row['pid'];
                    $tmpProc = BackendUtility::getProcessedValueExtra($table, $fCol, $row[$fCol], 100, $row['uid'], true, $pageId);
                    $theData[$fCol] = $this->linkUrlMail(htmlspecialchars($tmpProc), $row[$fCol]);
                    if ($this->csvOutput) {
                        $row[$fCol] = BackendUtility::getProcessedValueExtra($table, $fCol, $row[$fCol], 0, $row['uid']);
                    }
                }
            }
            // Reset the ID if it was overwritten
            if ((string)$this->searchString !== '') {
                $this->id = $id_orig;
            }
            // Add row to CSV list:
            if ($this->csvOutput) {
                $this->addToCSV($row);
            }
            // Add classes to table cells
            $this->addElement_tdCssClass[$titleCol] = 'col-title col-responsive' . $localizationMarkerClass;
            $this->addElement_tdCssClass['__label'] = $this->addElement_tdCssClass[$titleCol];
            $this->addElement_tdCssClass['_CONTROL_'] = 'col-control';
            if ($this->getModule()->MOD_SETTINGS['clipBoard']) {
                $this->addElement_tdCssClass['_CLIPBOARD_'] = 'col-clipboard';
            }
            $this->addElement_tdCssClass['_PATH_'] = 'col-path';
            $this->addElement_tdCssClass['_LOCALIZATION_'] = 'col-localizationa';
            $this->addElement_tdCssClass['_LOCALIZATION_b'] = 'col-localizationb';
            // Create element in table cells:
            $theData['uid'] = $row['uid'];
            if (isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])
                && isset($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'])
                && $table !== 'pages_language_overlay'
            ) {
                $theData['_l10nparent_'] = $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']];
            }
    
            $tagAttributes = array_map(
                function ($attributeValue) {
                    if (is_array($attributeValue)) {
                        return implode(' ', $attributeValue);
                    }
                    return $attributeValue;
                },
                $tagAttributes
            );
            DebuggerUtility::var_dump($theData, 'TheData in Render List Row');
            $rowOutput .= $this->addElement(1, $theIcon, $theData, GeneralUtility::implodeAttributes($tagAttributes, true));
            // Finally, return table row element:
            return $rowOutput;
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
            $queryParameters['where'] = [''];

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($queryParameters['table']);
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(BackendWorkspaceRestriction::class));
            $queryBuilder
                ->select('*')
                ->from($queryParameters['table'])
                ->where(...$queryParameters['where']);            
            $this->totalItems = (int)$queryBuilder->count('*')
                ->execute()
                ->fetchColumn();
        }

         
    
      /**
     * Returns a QueryBuilder configured to select $fields from $table where the pid is restricted
     * depending on the current searchlevel setting.
     *
     * @param string $table Table name
     * @param int $pageId Page id Only used to build the search constraints, getPageIdConstraint() used for restrictions
     * @param string[] $additionalConstraints Additional part for where clause
     * @param string[] $fields Field list to select, * for all
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    public function getQueryBuilder(
        string $table,
        int $pageId,
        array $additionalConstraints = [],
        array $fields = ['*']
    ): QueryBuilder {
        $queryParameters = $this->buildQueryParameters($table, $pageId, $fields, $additionalConstraints);
        $queryParameters['where'] = [''];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($queryParameters['table']);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(BackendWorkspaceRestriction::class));
        $queryBuilder
            ->select(...$queryParameters['fields'])
            ->from($queryParameters['table'])
            ->where(...$queryParameters['where']);

        if (!empty($queryParameters['orderBy'])) {
            foreach ($queryParameters['orderBy'] as $fieldNameAndSorting) {
                list($fieldName, $sorting) = $fieldNameAndSorting;
                $queryBuilder->addOrderBy($fieldName, $sorting);
            }
        }

        if (!empty($queryParameters['firstResult'])) {
            $queryBuilder->setFirstResult((int)$queryParameters['firstResult']);
        }

        if (!empty($queryParameters['maxResults'])) {
            $queryBuilder->setMaxResults((int)$queryParameters['maxResults']);
        }

        if (!empty($queryParameters['groupBy'])) {
            $queryBuilder->groupBy($queryParameters['groupBy']);
        }

        return $queryBuilder;
    }


}





        //Making sure that the fields in the field-list ARE in the field-list from TCA!

        
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this, 'this');
        // $this->languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_layout.xlf');
        // $warningText = $this->languageService->getLL('deleteWarning');
        // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($warningText, 'Delete Warning Text');
        // return 'Output Of the Record List';

