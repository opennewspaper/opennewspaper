<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

class Tx_newspaper_Controller_SectionModuleController extends Tx_Extbase_MVC_Controller_ActionController {

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction() {
        // @todo Evaluate how the intval() call can be used with Extbase validators/filters
        $this->pageId = intval(t3lib_div::_GP('id'));

        $this->pageRenderer->addInlineLanguageLabelFile('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml');
    }

    /**
     * Action to create a new section
     */
    public function newAction() {
        $this->view->assign('sections', tx_newspaper_Section::getAllSections());
        $this->view->assign('template_sets', tx_newspaper_smarty::getAvailableTemplateSets());
        tx_newspaper::devlog('template_sets', tx_newspaper_smarty::getAvailableTemplateSets());
        $this->view->assign('article_types', tx_newspaper_ArticleType::getArticleTypes());
        tx_newspaper::devlog('article_types', tx_newspaper_ArticleType::getArticleTypes());
        $this->view->assign('REQUEST', $_REQUEST);
        $module_request = $_REQUEST['tx_newspaper_txnewspapermmain_newspapersectionmodule'];
        if ($module_request) {
            $this->view->assign('module_request', $module_request);
            if (self::isValidRequest($module_request)) {

            }
        }
    }

    /**
     * Action to edit existing section
     */
    public function editAction() {
        $this->view->assign('sections', tx_newspaper_Section::getAllSections());
    }

    /**
     * Processes a general request. The result can be returned by altering the given response.
     *
     * @param Tx_Extbase_MVC_RequestInterface $request The request object
     * @param Tx_Extbase_MVC_ResponseInterface $response The response, modified by this handler
     * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType if the controller doesn't support the current request type
     * @return void
     */
    public function processRequest(Tx_Extbase_MVC_RequestInterface $request, Tx_Extbase_MVC_ResponseInterface $response) {

        $this->template = t3lib_div::makeInstance('template');
        $this->pageRenderer = $this->template->getPageRenderer();

        $GLOBALS['SOBE'] = new stdClass();
        $GLOBALS['SOBE']->doc = $this->template;

        parent::processRequest($request, $response);

        $pageHeader = $this->template->startpage(
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.title')
        );
        $pageEnd = $this->template->endPage();

        $response->setContent($pageHeader . $response->getContent() . $pageEnd);
    }


    private static function isValidRequest(array $request) {
        return true;
    }


    /** @var string Key of the extension this controller belongs to */
    protected $extensionName = 'newspaper';
    /** @var t3lib_PageRenderer */
    protected $pageRenderer;
    /** @var integer */
    protected $pageId;
    /** @var template */
    private $template;
}
