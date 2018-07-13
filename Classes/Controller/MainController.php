<?php
declare(strict_types=1);
namespace RubenSteeb\TestModuleRelations\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class MainController extends ActionController {
    /**
     * Default View Container
     * 
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     * 
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * set up the doc header properly
     * 
     * @param ViewInterafce $view
     * @return void
     */
    protected function initializeView(ViewInterface $view) {
        /** @var BackendTemplateView */
        parent::initalizeView($view);
        if ($view instanceof BackendTemplateView) {
			$view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');			
		}
    }

    public function indexAction() {

    }
}