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

        $this->pageRenderer->addInlineLanguageLabelFile('EXT:workspaces/Resources/Private/Language/locallang_sectionmodule.xml');
    }

    /**
     * Simple action to list some stuff
     */
    public function listAction() {
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

        tx_newspaper::devlog('request', $request);
        $this->template = t3lib_div::makeInstance('template');
        $this->pageRenderer = $this->template->getPageRenderer();

        $GLOBALS['SOBE'] = new stdClass();
        $GLOBALS['SOBE']->doc = $this->template;

        parent::processRequest($request, $response);

        $pageHeader = $this->template->startpage(
            $GLOBALS['LANG']->sL('LLL:EXT:workspaces/Resources/Private/Language/locallang_sectionmodule.xml:module.title')
        );
        $pageEnd = $this->template->endPage();

        $response->setContent($pageHeader . $response->getContent() . $pageEnd);
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
