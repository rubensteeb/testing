<?php
declare(strict_types=1);
namespace RubenSteeb\Testing\Controller;

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
     * @var \TYPO3\CMS\Lang\LanguageService
     */
    protected $languageService;

    /**
     * BackendTemplateContainer
     * 
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @param \TYPO3\CMS\Lang\LanguageService
     */
    public function injectLanguageService(\TYPO3\CMS\Lang\LanguageService $languageService) {
        $this->languageService = $languageService;
    }
    /**
     * set up the doc header properly
     * 
     * @param ViewInterafce $view
     * @return void
     */
    protected function initializeView(ViewInterface $view) {
        /** @var BackendTemplateView */
        if ($view instanceof BackendTemplateView) {
			$view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');			
		}
    }


    public function indexAction() {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->languageService, 'LangService', 'LangService');
    }
}