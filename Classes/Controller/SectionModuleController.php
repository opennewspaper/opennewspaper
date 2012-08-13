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
        $this->view->assign('REQUEST', $_REQUEST);

        $this->view->assign('sections', tx_newspaper_Section::getAllSections());
        $this->view->assign('template_sets', tx_newspaper_smarty::getAvailableTemplateSets());
        $this->view->assign('article_types', tx_newspaper_ArticleType::getArticleTypes());

        $module_request = $_REQUEST['tx_newspaper_txnewspapermmain_newspapersectionmodule'];
        if ($module_request) {
            $this->view->assign('module_request', $module_request);
            if (self::isValidRequest($module_request)) {
                self::createSection($module_request);
            } else {
                $this->view->assign('invalid_input', 1);
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
        if ($request['articlelist_type'] == 'none') return false;
        if (empty($request['section_name'])) return false;
        return true;
    }

    private static function createSection(array $request) {

        $section = self::createSectionObject($request);

        // @todo Article lists; ask oli

        self::activatePageZones($section);

        try {
            $parent_page = $section->getParentSection()->getTypo3PageID();
        } catch (tx_newspaper_IllegalUsageException $e) {
            // @todo error handlings if multiple pages for the parent section
            die("Nope!");
        }

        $record = tx_newspaper::selectOneRow('*', 'pages', "uid = $parent_page");

        $record['pid'] = $record['uid'];
        unset($record['uid']);
        $record['crdate'] = $record['tstamp'] = time();
        $record['cruser_id'] = tx_newspaper::getBeUserUid();
        $record['title'] = $section->getSectionName();
        $record['TSconfig'] = null;
        $record['is_siteroot'] = 0;
        $record['tx_newspaper_associated_section'] = $section->getUid();

        // @todo check that page does not yet exist
        $new_page_id = tx_newspaper::insertRows('pages', $record);

        $record = tx_newspaper::selectOneRow(
            '*', 'tt_content',
            "pid = $parent_page AND CType = 'list' AND list_type = 'newspaper_pi1'"
        );

        unset($record['uid']);
        $record['pid'] = $new_page_id;
        $record['crdate'] = $record['tstamp'] = time();
        $record['cruser_id'] = tx_newspaper::getBeUserUid();

        $ce_uid = tx_newspaper::insertRows('tt_content', $record);
    }

    private static function createSectionObject(array $request) {
        $section = new tx_newspaper_Section();

        $section->setAttribute('section_name', $request['section_name']);
        $section->setAttribute('parent_section', $request['parent_section']);
        $section->setAttribute('show_in_list', $request['show_in_section_list'] ? 1 : 0);
        $template_sets = tx_newspaper_smarty::getAvailableTemplateSets();
        $section->setAttribute('template_set', $template_sets[$request['template_set']]);
        $section->setAttribute('default_articletype', $request['article_type']);

        $section->store();

        return $section;
    }

    private static function activatePageZones(tx_newspaper_Section $section) {
        foreach ($section->getParentSection()->getActivePages() as $page) {
            $section->activatePage($page->getPageType());
            foreach ($page->getActivePageZones() as $page_zone) {
                $section->getSubPage($page->getPageType())->activatePagezone($page_zone->getPageZoneType());
            }
        }
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
