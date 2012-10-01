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
        $this->pageId = intval(t3lib_div::_GP('id'));
        $this->pageRenderer->addInlineLanguageLabelFile('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml');
    }

    /**
     * Action to create a new section
     */
    public function newAction() {
        $module_request = $_REQUEST['tx_newspaper_txnewspapermmain_newspapersectionmodule'];
        $this->view->assign('module_request', $module_request);

        $this->view->assign('sections', tx_newspaper_Section::getAllSectionsWithRestrictions(false));
        $this->view->assign('template_sets', tx_newspaper_smarty::getAvailableTemplateSets());
        $this->view->assign('article_types', tx_newspaper_ArticleType::getArticleTypesRestricted());

        if ($this->isValidRequest($module_request)) {
            try {
                $this->createSection($module_request);
                $this->flashMessageContainer->add(self::getSectionMessage('success', $module_request['section_name']));
                $this->view->assign('module_request', array());
            } catch (tx_newspaper_Exception $e) {
                $this->addError(
                    '<p>' . $e->getMessage() . '</p>' .
                    '<p>' . str_replace("\n", "<br />\n", $e->getTraceAsString()) . '</p>',
                    'system_error'
                );
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


    private function isValidRequest($request) {

        if (!is_array($request)) return false;

        $ok = true;
        if ($request['articlelist_type'] == 'none') {
            $this->addError(
                $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.error.select_articlelist_type'),
                'user_error'
            );
            $this->view->assign('invalid_articlelist_type', 1);
            $ok = false;
        }
        if (empty($request['section_name'])) {
            $this->addError(
                $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.error.enter_section_name'),
                'user_error'
            );
            $this->view->assign('invalid_section_name', 1);
            $ok = false;
        }
        return $ok;
    }

    private function createSection(array $request) {

        $parent = new tx_newspaper_Section($request['parent_section']);
        try {
            $parent_page = $parent->getTypo3PageID();
        } catch (tx_newspaper_IllegalUsageException $e) {
            $this->addError(
                self::getSectionMessage('error.multiple_section_pages', $request['section_name'], $parent->getSectionName()),
                'system_error'
            );
            return;
        }

        $section = self::createSectionObject($request);

        $this->createArticleList($section, $request['articlelist_type']);

        self::activatePageZones($section);

        $new_page_id = self::createTypo3Page($parent_page, $section);

        $ce_id = self::createContentElement($parent_page, $new_page_id);
    }

    private function addError($message, $key) {
        $this->flashMessageContainer->add(
            $message,
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.error.'.$key),
            t3lib_FlashMessage::ERROR
        );
    }

    private static function createSectionObject(array $request) {
        $section = new tx_newspaper_Section();

        $section->setAttribute('section_name', $request['section_name']);
        $section->setAttribute('parent_section', $request['parent_section']);
//        $section->setAttribute('show_in_list', $request['show_in_list'] ? 1 : 0);
        $section->setAttribute('show_in_list', 1);
//        $template_sets = tx_newspaper_smarty::getAvailableTemplateSets();
//        $section->setAttribute('template_set', $template_sets[$request['template_set']]);
        $section->setAttribute('default_articletype', $request['default_articletype']);

        $section->store();

        return $section;
    }

    private function createArticleList(tx_newspaper_Section $section, $articlelist_type) {

        if (!is_subclass_of($articlelist_type, 'tx_newspaper_ArticleList')) {
            throw new tx_newspaper_InconsistencyException(
                "Articlelist type $articlelist_type, as defined in Fluid template new.html, is not an instance of tx_newspaper_Articlelist."
            );
        }

        $section->assignDefaultArticleList();
        $section->replaceArticleList(new $articlelist_type(0, $section));
    }

    private static function activatePageZones(tx_newspaper_Section $section) {
        foreach ($section->getParentSection()->getActivePages() as $page) {
            $section->activatePage($page->getPageType());
            foreach ($page->getActivePageZones() as $page_zone) {
                $section->getSubPage($page->getPageType())->activatePagezone($page_zone->getPageZoneType());
            }
        }
    }

    private static function createTypo3Page($parent_page, tx_newspaper_Section $section)  {

        $record = tx_newspaper_DB::getInstance()->selectZeroOrOneRows(
            'uid', 'pages',
            "pid = $parent_page AND title='" . $section->getSectionName() . "' AND NOT tx_newspaper_associated_section"
        );
        if (!empty($record)) {
            return $record['uid'];
        }

        $record = tx_newspaper_DB::getInstance()->selectOneRow('*', 'pages', "uid = $parent_page");

        $record['pid'] = $record['uid'];
        unset($record['uid']);
        $record['crdate'] = $record['tstamp'] = time();
        $record['cruser_id'] = tx_newspaper::getBeUserUid();
        $record['title'] = $section->getSectionName();
        $record['TSconfig'] = null;
        $record['is_siteroot'] = 0;
        $record['tx_newspaper_associated_section'] = $section->getUid();

        return tx_newspaper_DB::getInstance()->insertRows('pages', $record);
    }

    private static function createContentElement($parent_page, $new_page_id) {
        $record = tx_newspaper_DB::getInstance()->selectOneRow(
            '*', 'tt_content',
            "pid = $parent_page AND CType = 'list' AND list_type = 'newspaper_pi1'"
        );

        unset($record['uid']);
        $record['pid'] = $new_page_id;
        $record['crdate'] = $record['tstamp'] = time();
        $record['cruser_id'] = tx_newspaper::getBeUserUid();

        return tx_newspaper_DB::getInstance()->insertRows('tt_content', $record);
    }

    private static function getSectionMessage($key, $section, $parent = '') {
        return str_replace(
            '###SECTION###', $section, str_replace(
                '###PARENT###', $parent, $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.'.$key
                )
            )
        );
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
