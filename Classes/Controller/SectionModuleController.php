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
        $this->sections = tx_newspaper_Section::getAllSectionsWithRestrictions(false, 'section_name', 'articlelist');
        usort(
            $this->sections,
            function(tx_newspaper_Section $s1, tx_newspaper_Section $s2) { return $s1->getFormattedRootline() > $s2->getFormattedRootline(); }
        );
        $this->module_request = $_REQUEST['tx_newspaper_txnewspapermmain_newspapersectionmodule'];
    }

    /**
     * Action to create a new section
     */
    public function newAction() {

        $this->view->assign('sections', $this->sections);
        $this->view->assign('module_request', $this->module_request);
        $this->view->assign('template_sets', tx_newspaper_smarty::getAvailableTemplateSets());
        $this->view->assign('article_types', tx_newspaper_ArticleType::getArticleTypesRestricted());

        if (!$this->isValidRequest($this->module_request)) return;

        try {
            $transaction = new tx_newspaper_DBTransaction();

            $this->createSection($this->module_request);

            $transaction->commit();

            $this->flashMessageContainer->add(self::getSectionMessage('success', $this->module_request['section_name']));
            $this->view->assign('module_request', array());

        } catch (tx_newspaper_Exception $e) {
            $this->addError(
                '<p>' . $e->getMessage() . '</p>' .
                '<p>' . str_replace("\n", "<br />\n", $e->getTraceAsString()) . '</p>',
                'system_error'
            );
        }

    }

    /**
     * Action to edit existing section
     */
    public function editAction() {

        $this->view->assign('sections', $this->sections);

        if (intval($this->module_request['section'])) {
            $section = new tx_newspaper_Section($this->module_request['section']);
            $this->view->assign('parent_sections', self::pruneChildSections($section, $this->sections));
            $this->view->assign('parent_section', $section->getParentSection());
            $this->view->assign('description', $section->getAttribute('description'));
            $this->view->assign('articlelist_type', get_class($section->getArticleList()));
            $this->view->assign('default_articletype', $section->getDefaultArticleType()->getUid());
            $this->view->assign('article_types', tx_newspaper_ArticleType::getArticleTypesRestricted());

            if (isset($this->module_request['section_name'])) {

                $transaction = new tx_newspaper_DBTransaction();

                $this->changeSectionName($section);
                $this->changeDescription($section);
                $this->changeParent($section);
                $this->changeArticleListType($section);
                $this->changeDefaultArticleType($section);

                $section->store();

                $transaction->commit();

            } else {
                $this->module_request['section_name'] = $section->getSectionName();
            }

            $this->view->assign('module_request', $this->module_request);

        }
    }

    /**
     * Action to delete existing section
     */
    public function deleteAction() {
        $this->view->assign('sections', $this->sections);
        if (!empty($this->module_request['section'])) {
            $section = new tx_newspaper_Section($this->module_request['section']);
            $children = $section->getChildSections();
            $loose_articles = self::getArticlesWithOnlySection($section);
            $this->view->assign('child_sections', $children);
            $this->view->assign('loose_articles', $loose_articles);
            $this->view->assign('affected_section', $section);
            $this->view->assign('affected_pages', $section->getSubPages());
            $this->view->assign('affected_articles', $section->getArticles(0));

            if (intval($this->module_request['confirm']) == 1 && empty($children) && empty($loose_articles)) {

                try {
                    $section_name = $section->getSectionName();
                    $transaction = new tx_newspaper_DBTransaction();
                    self::deleteSection($section);
                    $transaction->commit();

                    $this->flashMessageContainer->add($section_name, 'Deleted section');
                    $this->view->assign('deleted', 1);
                } catch (tx_newspaper_Exception $e) {
                    $this->flashMessageContainer->add($e->getMessage(), 'Deleting section failed', t3lib_FlashMessage::ERROR);
                }

            }
        }
        $this->view->assign('module_request', $this->module_request);
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

    ////////////////////////////////////////////////////////////////////////////

    private function addError($message, $key) {
        $this->flashMessageContainer->add(
            $message,
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.error.'.$key),
            t3lib_FlashMessage::ERROR
        );
    }

    private static function getSectionMessage($key, $section, $parent = '') {
        return str_replace(
            '###SECTION###', $section, str_replace(
                '###PARENT###', $parent, $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.'.$key
                )
            )
        );
    }

    //////// new action

    private function isValidRequest($request, $show_message = true) {

        if (!is_array($request)) return false;

        if ($request == array('action' => 'new', 'controller' => 'SectionModule')) return false;

        $ok = true;
        if ($request['articlelist_type'] == 'none') {
            $show_message && $this->addError(
                $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.error.select_articlelist_type'),
                'user_error'
            );
            $this->view->assign('invalid_articlelist_type', 1);
            $ok = false;
        }
        if (empty($request['section_name'])) {
            $show_message && $this->addError(
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
            throw $e;
        }

        $section = self::createSectionObject($request);

        $this->createArticleList($section, $request['articlelist_type']);

        self::activatePageZones($section);

        $new_page_id = self::createTypo3Page($parent_page, $section);

        $ce_id = self::createContentElement($parent_page, $new_page_id);
    }

    private static function createSectionObject(array $request) {
        $section = new tx_newspaper_Section();
        $section->setAttribute('show_in_list', 1);
        $section->setAttribute('section_name', $request['section_name']);
        $section->setAttribute('parent_section', $request['parent_section']);
        $parent = $section->getParentSection();
        $section->setAttribute('template_set', $parent->getAttribute('template_set'));
        $section->setAttribute('default_articletype', $request['default_articletype']);
        $section->setAttribute('description', $request['description']);
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

    //////// edit action

    /**
     *  Removes the children of \p $section from the array \p $sections, including \p $section.
     *
     *  This function has to take two annoying facts into account:
     *  - \c $section->getChildSections() does not include \c $section
     *  - \c $sections and \c $section->getChildSections() contain partially populated
     *    objects, so a test using \c in_array does not work.
     *  Therefore, it is somewhat complicated.
     *  @param tx_newspaper_Section $section The section which gets its parent section changed
     *  @param array $sections All sections
     *  @return array All sections minus \c $section and its child sections
     */
    private static function pruneChildSections(tx_newspaper_Section $section, array $sections) {
        $child_uids = array_map(
            function(tx_newspaper_Section $s) { return $s->getUid(); },
            array_merge($section->getChildSections(true), array($section))
        );
        return array_filter(
            $sections,
            function(tx_newspaper_Section $s) use ($child_uids) { return !in_array($s->getUid(), $child_uids); }
        );
    }

    private function changeSectionName(tx_newspaper_Section &$section) {

        if (!$this->isAttributeChanged($section, 'section_name')) return;

        tx_newspaper_DB::getInstance()->updateRows(
            'pages', 'uid = ' . $section->getTypo3PageID(),
            array('title' => $this->module_request['section_name'])
        );

    }

    private function changeDescription(tx_newspaper_Section &$section) {
        $this->isAttributeChanged($section, 'description');
    }

    /**
     * Checks whether an attribute has been changed, and displays a FlashMessage if so.
     *
     * @param tx_newspaper_Section $section
     * @param $attribute
     * @return bool Whether the attribute is actually changed
     */
    private function isAttributeChanged(tx_newspaper_Section &$section, $attribute) {
        $this->view->assign("old_$attribute", $section->getAttribute($attribute));
        $this->view->assign("new_$attribute", $this->module_request[$attribute]);

        if ($section->getAttribute($attribute) == $this->module_request[$attribute]) return false;

        $old_value = $section->getAttribute($attribute);
        $section->setAttribute($attribute, $this->module_request[$attribute]);

        $this->flashMessageContainer->add(
            $old_value . ' -> ' . $this->module_request[$attribute],
            $GLOBALS['LANG']->sL("LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.${attribute}_changed")
        );

        return true;
    }

    private function changeParent(tx_newspaper_Section &$section) {

        if (!intval($this->module_request['parent_section'])) return;

        $old_parent = $section->getParentSection();
        if ($old_parent->getUid() == intval($this->module_request['parent_section'])) return;

        $new_parent = new tx_newspaper_Section($this->module_request['parent_section']);

        $section->setAttribute('parent_section', $new_parent->getUid());

        tx_newspaper_DB::getInstance()->updateRows(
            'pages', 'uid = ' . $section->getTypo3PageID(),
            array('pid' => $new_parent->getTypo3PageID())
        );

        $this->flashMessageContainer->add(
            $old_parent->getFormattedRootline() . ' -> ' . $new_parent->getFormattedRootline(),
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.parent_section_changed')
        );
    }

    private function changeArticleListType(tx_newspaper_Section &$section) {
        if (strtolower(substr($this->module_request['articlelist_type'], 0, 24)) != 'tx_newspaper_articlelist') return;

        $old_type = get_class($section->getArticleList());
        $articlelist_type = $this->module_request['articlelist_type'];
        $section->replaceArticleList(new $articlelist_type(0, $section));

        $this->flashMessageContainer->add(
            $old_type . ' -> ' . get_class($section->getArticleList()),
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.articlelist_changed')
        );
    }

    private function changeDefaultArticleType(tx_newspaper_Section &$section) {
        if (!intval($this->module_request['default_articletype'])) return;
        $old_article_type_id = $section->getAttribute('default_articletype');
        if ($old_article_type_id == $this->module_request['default_articletype']) return;
        $section->setAttribute('default_articletype', intval($this->module_request['default_articletype']));
        $old_type = new tx_newspaper_ArticleType($old_article_type_id);
        $new_type = new tx_newspaper_ArticleType($this->module_request['default_articletype']);
        $this->flashMessageContainer->add(
            $old_type->getAttribute('title') . ' -> ' . $new_type->getAttribute('title'),
            $GLOBALS['LANG']->sL('LLL:EXT:newspaper/Resources/Private/Language/locallang.xml:module.section.default_type_changed')
        );

    }

    //////// delete action

    private static function deleteSection(tx_newspaper_Section $section) {
        tx_newspaper_DB::getInstance()->deleteRows(
            'tt_content',
                "pid = " . $section->getTypo3PageID() . " AND CType = 'list' AND list_type = 'newspaper_pi1'"
        );

        tx_newspaper_DB::getInstance()->deleteRows('pages', array($section->getTypo3PageID()));

        foreach ($section->getSubPages() as $page) {
            $page->delete();
        }

        tx_newspaper_DB::getInstance()->deleteRows('tx_newspaper_article_sections_mm', 'uid_foreign = ' . $section->getUid());

        $section->getArticleList()->delete();

        $section->setAttribute('deleted', 1);
        $section->store();
    }

    private static function getArticlesWithOnlySection(tx_newspaper_Section $section) {
        return array_filter(
            $section->getArticles(0),
            function(tx_newspaper_Article $article) { return sizeof($article->getSections()) <= 1; }
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
    /** @var tx_newspaper_Section[] */
    private $sections = array();
    /** @var array */
    private $module_request = array();
}
