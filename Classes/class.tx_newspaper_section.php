<?php
/**
 *  \file class.tx_newspaper_section.php
 *
 *  This file is part of the TYPO3 extension "newspaper".
 *
 *  Copyright notice
 *
 *  (c) 2008 Lene Preuss, Oliver Schroeder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *
 *  \author Lene Preuss <lene.preuss@gmail.com>
 *  \date Jan 8, 2009
 */

/// A section of an online edition of a newspaper
class tx_newspaper_Section implements tx_newspaper_StoredObject {

    /// Construct a tx_newspaper_Section given the UID of the SQL record
    public function __construct($uid = 0) {
        if ($uid) {
            $this->setUid($uid);
        }
    }

    /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        try {
            $this->getAttribute('uid');
        } catch (tx_newspaper_Exception $e) { }
        return get_class($this) . '-object ' . $this->getUid() . " \n" .
               'attributes: ' . print_r($this->attributes, 1) . "\n";
    }

    private static $disable_attribute_cache = false;
    public static function disableAttributeCache() {
        self::$disable_attribute_cache = true;
    }
    public function getAttribute($attribute) {
        if (!$this->attributes || self::$disable_attribute_cache) {
            $this->attributes = tx_newspaper::selectOneRow(
                '*', $this->getTable(), 'uid = ' . $this->getUid()
            );
        }

        if (!array_key_exists($attribute, $this->attributes)) {
            throw new tx_newspaper_WrongAttributeException($attribute);
        }

        return $this->attributes[$attribute];
    }

    /** No tx_newspaper_WrongAttributeException here. We want to be able to set
     *  attributes, even if they don't exist beforehand.
     */
    public function setAttribute($attribute, $value) {
        if (!$this->attributes && $this->getUid() > 0) {
            $this->attributes = tx_newspaper::selectOneRow(
                '*', $this->getTable(), 'uid = ' . $this->getUid()
            );
        }

        $this->attributes[$attribute] = $value;
    }

    /// Write or overwrite Section data in DB, return UID of stored record
    public function store() {

        tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));

        if (!$this->getUid()) {
            $this->setUid(tx_newspaper::insertRows($this->getTable(), $this->attributes));
        } else {
            tx_newspaper::updateRows($this->getTable(), 'uid = ' . $this->getUid(), $this->attributes);
        }

        //    empty attributes array so it can be read in full at next access
        $this->attributes = array();
    }

    /// \return true if section can be accessed (FE/BE use enableFields)
    public function isValid() {
        // check if section is valid
        try {
            $tmp = $this->getAttribute('uid'); // getAttribute forces the object to be read from database
            return true;
        } catch (tx_newspaper_EmptyResultException $e) {
            return false;
        }
    }

    public function getTitle() {
        return tx_newspaper::getTranslation('title_' . $this->getTable());
    }

    /// assigns a default article list to this section
    public function assignDefaultArticleList() {
// \todo make configurable which article list type is default
// \todo: how to add a default note like "Article list for section 'dummy'"?
        $al = new tx_newspaper_ArticleList_Semiautomatic(0, $this);
        $this->replaceArticleList($al); // assign new article list to this section
    }

    /// Replace the current article list (if any) with the given new article list
    /** The function first removes the old article list (if any) and then assign the new article list.
     *  As some attributes are changed (crdate f.ex.) the new article gets stored in this function.
     * @param $new_al Article list object: new article list
     * @return uid of Abstract article list
     */
    public function replaceArticleList(tx_newspaper_articlelist $new_al) {

        if (get_class($new_al) == get_class($this->articlelist)) {
            return $this->getArticleList()->getAbstractUid();
        }

        $articles = array();

        try {
            $current_al = $this->getArticleList(); // get current article list

            if ($current_al->getTable() == 'tx_newspaper_articlelist_semiautomatic') {
                // store articles if semiautomatic article list, might be copied to manual list later ...
                $articles = $current_al->getArticles($current_al->getNumArticles());
            }

            // "delete" (= set deleted flag) previous concrete article list before writing the new one
            // concrete article list must be deleted first (otherwise data for concrete article list can't be obtained from abstract article list)
            tx_newspaper::updateRows(
                $current_al->getTable(),
                'uid=' . $current_al->getUid(),
                array('deleted' => 1)
            );
            $this->articlelist = null; // well, this list has just been deleted on the database
        } catch (tx_newspaper_EmptyResultException $e) {
            // no article list assigned so far, either new section or the article list was deleted for some reason
        }

        // "delete" (= set deleted flag) all abstract article lists assigned to this section, before writing the new one
        // just deleting the current article list would do, but this deletes potential orphan article lists for this section too
        tx_newspaper::updateRows(
            'tx_newspaper_articlelist',
            'section_id=' . $this->getUid(),
            array('deleted' => 1)
        );

        /// try to re-activate an old deleted article list for the new article list type

        // read newest abstract article list of new article list's type (record is deleted; was before or was set deleted by the updareRows() abobe)
        $al_abstract = tx_newspaper::selectRowsDirect(
            '*',
            'tx_newspaper_articlelist',
            'list_table="' .  $new_al->getTable() . '" AND section_id=' . $this->getUid(),
            '',
            'crdate DESC, tstamp DESC',
            '1'
        );
        if (sizeof($al_abstract) > 0) {
            // try to re-activate deleted article list
            // check if concrete article list is still available
            $al_concrete = tx_newspaper::selectRowsDirect(
                '*',
                $new_al->getTable(),
                'uid=' . $al_abstract[0]['list_uid']
            );
            if (sizeof($al_concrete) > 0) {
                tx_newspaper::updateRows( // undelete concrete article list
                    $new_al->getTable(),
                    'uid=' . $al_abstract[0]['list_uid'],
                    array('deleted' => 0)
                );
                tx_newspaper::updateRows( // undelete abstract article list
                    'tx_newspaper_articlelist',
                    'uid=' . $al_abstract[0]['uid'],
                    array('deleted' => 0)
                );

                $reactivatedArticlelist = tx_newspaper_ArticleList_Factory::getInstance()->create($al_abstract[0]['uid']);
                $reactivatedArticlelist->addArticlesToEmptyManualArticlelist($articles);

                return $al_abstract[0]['uid']; // uid of abstract article list
            }
        }

        // no article list found to re-activate, so create a new one
        $new_al->store(); // store new article list

        // set title for this articlelist
        $title = tx_newspaper::getTranslation('title_section_articlelist');
        $title = str_replace('###SECTION###', $this->getAttribute('section_name'), $title);
        $title = str_replace('###ARTICLELIST_TYPE###', $new_al->getTitle(), $title);
        tx_newspaper::updateRows(
            'tx_newspaper_articlelist',
            'uid=' . $new_al->getAbstractUid(),
            array(
                'notes' => $title,
            )
        );

        //re-read articlelist in order get read the full set of attributes
        // @todo Lene: Why do I have to reload the list?
        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($new_al->getAbstractUid());

        $al->addArticlesToEmptyManualArticlelist($articles);

        return $al->getAbstractUid();

    }

    /** @return tx_newspaper_ArticleList The section's article list */
    public function getArticleList() {
        if (!$this->articlelist) {
            $list = tx_newspaper::selectOneRow('uid', self::$list_table, 'section_id  = ' . $this->getUid());
            $this->articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create($list['uid'], $this);
        }
        return $this->articlelist;
    }

    /** @return tx_newspaper_Section The parent node in the section tree. */
    public function getParentSection() {
        if (!intval($this->getAttribute('parent_section'))) return null;
        return new tx_newspaper_Section($this->getAttribute('parent_section'));
    }

    public function setParentSection(tx_newspaper_Section $new_parent) {
        $this->setAttribute('parent_section', $new_parent->getUid());
        $this->store();
    }

    /**
     *  Get all Sections which have \c $this as ancestor
     *  @param bool $recursive If true, return all inheriting sections down to the
     *         leaves. Otherwise, return the direct children of this Section.
     *  @return tx_newspaper_Section[] Flat array of Section objects under \p $this.
     *         Parental relations are \em not preserved.
     */
    public function getChildSections($recursive = false) {

        $row = tx_newspaper::selectRows(
                'uid', $this->getTable(),
                'parent_section = ' . $this->getUid(),
                '', 'sorting'
            );

        $sections = array();
        if ($row) foreach ($row as $section_uid) {
            $child =  new tx_newspaper_Section($section_uid['uid']);
            $sections[] = $child;
            if ($recursive) {
                $sections = array_merge($sections, $child->getChildSections(true));
            }
        }

        return $sections;
    }

    public function getRootLine() {
        return array_slice($this->getSectionPath(), 1);
    }

    /// \return uid of parent abstract record for concrete article list associated with section
    public function getAbstractArticleListUid() {
        return $this->getAttribute('articlelist');
    }

    /**
     * Activate a page for this section
     * @param tx_newspaper_PageType $type
     * @return bool true if page was activated, false if page has been active already
     */
    public function activatePage(tx_newspaper_PageType $type) {

        if ($this->getSubPage($type)) {
            return false; // Page has been activated already
        }

        // Check if a deleted page can be re-activated
        $row = tx_newspaper::selectRowsDirect(
            '*',
            'tx_newspaper_page',
            'section=' . $this->getUid() . ' AND pagetype_id=' . $type->getUid() . ' AND deleted=1',
            '',
            'uid DESC',
            '1'
        );

        if ($row) {
            // Re-activate deleted page
            tx_newspaper::updateRows(
                'tx_newspaper_page',
                'uid=' . $row[0]['uid'],
                array('deleted' => 0, 'tstamp' => time())
            );
            $p = new tx_newspaper_Page($this, $type);
        } else {
            // Create new page
            $p = new tx_newspaper_Page($this, $type);
            $p->store();
            $p->setAttribute('crdate', time());
            $p->setAttribute('tstamp', time());
            $p->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
            $p->store();
        }

        $this->subPages[] = $p; // Add page to subPages array, so it's available right away

        return true;
    }

    /// Returns all pages attached to the current section
    /** @return tx_newspaper_Page[] */
    public function getSubPages() {
        if (!$this->subPages) {
            $row = tx_newspaper::selectRows(
                'uid', 'tx_newspaper_page',
                'section = ' . $this->getAttribute('uid')
            );
             foreach ($row as $record) {
                 $this->subPages[] = new tx_newspaper_Page((int)$record['uid']);
             }
        }
         return $this->subPages;
     }

     /// Finds the page under the current section that has the required page type.
     public function getSubPage(tx_newspaper_PageType $type) {
         foreach ($this->getSubPages() as $page) {
             if ($page->getPageType()->getUid() == $type->getUid())
                 return $page;
         }
         return null;
     }

     /// Finds a pages of specified page type under the current section or its children.
     /** If this section has the specified page type activated, that page is returned.
      *  Else all activated pages in the child sections are returned. Each section
      *  is followed down the section tree until a desired page is found.
      */
     public function getSubPagesRecursively(tx_newspaper_PageType $type) {
         $sub_page = $this->getSubPage($type);
         if ($sub_page instanceof tx_newspaper_Page) return array($sub_page);

         $sub_pages = array();
         foreach ($this->getChildSections() as $sub_section) {
             $sub_pages = array_merge($sub_pages, $sub_section->getSubPagesRecursively($type));
         }
         return $sub_pages;
     }

    /**
     * gets an array of sections up the rootline
     * @return tx_newspaper_Section[] up the rootline
     */
    public function getSectionPath(array $path = array()) {
        $path[] = $this;
        if (is_null($this->getParentSection())) return $path;
        return $this->getParentSection()->getSectionPath($path);
    }

    /// \return The UID of the associated Typo3 page
    public function getTypo3PageID() {
        try {
            $row = tx_newspaper::selectOneRow(
                'uid', 'pages','tx_newspaper_associated_section = ' . $this->getUid()
            );
        } catch (tx_newspaper_DBException $e) {
            throw new tx_newspaper_IllegalUsageException(
                'Section number ' . $this->getUid() . ', "' . $this->getAttribute('section_name') .
                '", appears to have no Typo3 page associated with it. Please create a page and ' .
                ' choose Section ' . $this->getUid() . ' in the "Extended" tab of the' .
                ' "Page Properties" of that Typo3 page.'
            );
        }
        return intval($row['uid']);
    }

    /** \return the Article PageZone (PageZoneType has is_article set) on the
     *         Page marked as the Article Page, or \c null.
     */
    public function getDefaultArticle() {
        foreach ($this->getSubPages() as $sub_page) {
            foreach ($sub_page->getActivePageZones() as $pagezone) {
                if ($pagezone->getPageZoneType()->getAttribute('is_article')) {
                    return $pagezone;
                }
            }
        }
        return null; // no default article found
    }

    /**
     * Get default article type for this section
     * @return tx_newspaper_ArticleType Default article type for this section
     */
    public function getDefaultArticleType() {
        return new tx_newspaper_ArticleType($this->getAttribute('default_articletype'));
    }

    public function getSectionName() { return $this->getAttribute('section_name'); }

     /** Create a new article based on TSConfig settings for musthave extras
      *  \param $at article type object
      */
     public function createNewArticle(tx_newspaper_articletype $at) {
         $new_article = new tx_newspaper_article();

         $new_article->setAttribute('crdate', time());
         $new_article->setAttribute('tstamp', time());

         $new_article->storeWithoutSavehooks(); // store article before adding the section (otherwise no uid available)
         $new_article->addSection($this);

         // \todo: check if extra is placed on $this->getDefaultArticle(), if default article are to be used at all
         // if yes: copy matching extras from default article if any, else create empty extra (as implemented below)

        ///    Create extras configured in TSConfig
        $must_have_extras = $at->getTSConfigSettings('musthave'); // read configured must have extras
        foreach($must_have_extras as $key => $default_extra) {
            // $default_extra contains a class name or
            // a class name extended with ":" and a default paragraph for the extra
            // TSConfig example: newspaper.articletype.[type].musthave = tx_newspaper_extra_image:-2
            list($extra_class, $paragraph) = explode(':', $default_extra);
            $paragraph = intval($paragraph);
#t3lib_div::devlog('createNewArticle', 'newspaper', 0, array('key' => $key, 'default_extra' => $default_extra, 'extra_class' => $extra_class, 'paragraph' => $paragraph, 'position'=>(self::getShiftValue(sizeof($must_have_extras)) << $key)));

            if (tx_newspaper::classImplementsInterface($extra_class, 'tx_newspaper_ExtraIface')) {
                self::addExtraToArticle($extra_class, $paragraph, $must_have_extras, $key, $new_article);
            } else {
                t3lib_div::devlog('Unknown Extra configured in TSConfig', 'newspaper', 3, array('tsconfig' => $extra_class, 'section' => $this, 'articletype' => $at));
            }
        }

         return $new_article;
     }

    private static function addExtraToArticle($extra_class, $paragraph, array $must_have_extras, $key, tx_newspaper_Article $new_article) {
        /** @var tx_newspaper_Extra $new_extra  */
        $new_extra = new $extra_class();

        //    I think this is needed before I can safely setAttribute(). Not sure. Anyway, BSTS.
        $new_extra->store();

        $new_extra->setAttribute('crdate', time());
        $new_extra->setAttribute('tstamp', time());

        $new_extra->setAttribute('show_extra', 1); //todo: switch via tsconfig
        $new_extra->setAttribute('paragraph', $paragraph);
        $new_extra->setAttribute('position', self::calculateInsertPosition($must_have_extras, $key));

        $new_extra->store(); //    Final store()

        /// Write association table entry article -> extra
        /// \todo $new_article->relateExtra2Article($new_extra)?
        tx_newspaper::insertRows(
            $new_article->getExtra2PagezoneTable(),
            array('uid_local' => $new_article->getUid(), 'uid_foreign' => $new_extra->getExtraUid())
        );
    }

    private static function calculateInsertPosition(array $must_have_extras, $key) {
        return 1 << (self::getShiftValue(sizeof($must_have_extras)) * ($key + 1));
    }

    private static function getShiftValue($num_extras) {
        $num_bits = PHP_INT_SIZE*8;
        $max_shift = $num_bits/(intval($num_extras)+1);
        if ($max_shift < 1) return 1;
        if ($max_shift > 8) return 8;
    }

     public function getTable() {
        return tx_newspaper::getTable($this);
    }

    public function setUid($uid) { $this->uid = intval($uid); }
    public function getUid() { return $this->uid; }

    /**
     *  get active page uids for current Section
     *  @return tx_newspaper_Page[] Active pages for this section
     */
    public function getActivePages() {

        $p = new tx_newspaper_Page($this);

        $records = tx_newspaper::selectRows(
            'uid',
            $p->getTable(),
            'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid($p) . ' AND section=' . $this->getUid()
        );

        $list = array();
        foreach ($records as $record) {
            $list[] = new tx_newspaper_Page(intval($record['uid']));
        }
        return $list;
    }

    /// Get array with article objects assigned to this section (limited by $limit)
    public function getArticles($limit=10) {
        $limit = intval($limit);
        $row = tx_newspaper::selectRows(
            'tx_newspaper_article_sections_mm.uid_local',
            'tx_newspaper_article_sections_mm, tx_newspaper_article',
            'tx_newspaper_article_sections_mm.uid_foreign=' . $this->getUid() . ' AND tx_newspaper_article_sections_mm.uid_local=tx_newspaper_article.uid',
            '',
            '',
            $limit? $limit: ''
        );
//t3lib_div::devlog('s getArticles row', 'newspaper', 0, array('query' => tx_newspaper::$query, 'row' => $row));
        $list = array();
        for ($i = 0; $i < sizeof($row); $i++) {
            $list[] = new tx_newspaper_Article(intval($row[$i]['uid_local']));
        }
        return $list;
    }

    ///    Generate a URL which links to the "section overview" page of the Section
    public function getLink() {
        return tx_newspaper::typolink_url(
            array('id' => $this->getTypo3PageID())
        );
    }

    public function getTemplateSet() {
        if ($this->getAttribute('template_set')) $this->getAttribute('template_set');
        if (!$this->getParentSection()) return '';
        return $this->getParentSection()->getTemplateSet();
    }

    /** Get all tx_newspaper_Section records in the DB matching an SQL condition
     *  @param string $where SQL condition used to find the sections
     *  @param string $sort_by Field of the \c tx_newspaper_section SQL table to sort results by.
     *  @return tx_newspaper_Section[] Found section objects in the DB.
     */
    public static function getSectionsByCondition($where, $sort_by = '') {
        return array_map(
            function(array $record) { return new tx_newspaper_Section(intval($record['uid'])); },
            tx_newspaper_DB::getInstance()->selectRows('uid', 'tx_newspaper_section', $where, '', $sort_by)
        );
    }

    /** Get all tx_newspaper_Section records in the DB (if $sysfolder is false) or from the sysfolder (if $sysfolder is true)
     *  @param bool $articlesAllowedOnly if set to true only section with the show_in_list flag set are returned
     *  @param string $sort_by Field of the \c tx_newspaper_section SQL table to sort results by.
     *  @param bool $sysfolder If true only section on the sysfolder are fetched
     *  @return tx_newspaper_Section[] Section objects in the DB.
     */
    public static function getAllSections($articlesAllowedOnly = true, $sort_by = 'sorting', $sysfolder = true, $additional_where = '') {

        $where = '1' .
            ($articlesAllowedOnly? ' AND show_in_list=1' : '') .
            ($sysfolder? ' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Section()): '') .
            ($additional_where? " AND $additional_where": '');

        return self::getSectionsByCondition($where, $sort_by);
    }

    /**
     * Get all section the current BE user is permitted to access
     * User TSConfig: newspaper.bnaseSections = [uids]
     * Params: see getAllSections()
     * @static
     * @return array tx_newspaper_section's
     */
    public static function getAllSectionsWithRestrictions($articlesAllowedOnly=true, $sort_by='sorting', $additional_where = '') {
        return array_filter(
            self::getAllSections($articlesAllowedOnly, $sort_by, true, $additional_where),
            function(tx_newspaper_Section $s) { return $s->isSectionAccessGranted(); }
        );
    }

    /**
     * Check if current be_user is allowed to access $this section
     * @return bool True, if be_user is allowed to access to section, else false
     */
    public function isSectionAccessGranted() {
        return (in_array($this->getUid(), self::getBaseSectionTreeUids()));
    }

    /**
     * Get base sections (either root sections or configured by TSConfig) for article wizard
     * newspaper.baseSections = [uid1, ..., uidn]
     * newspaper.baseSectionsAsStartSection = [uid1, ..., [uidn]
     * A base section is a section users can get access to in order add articles in child sections
     * Only if newspaper.baseSectionAsStartSection is set, the base section can be accessed by the used
     * Example: see Article wizard in production list
     * @static
     * @return array Sections
     */
    public static function getBaseSections() {

        // Get base sections
        if ($GLOBALS['BE_USER'] && $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections')) {
            // Read User TSConfig for base sections: get uids of base sections
            $baseSectionUids = t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections'));
        } else {
            // No base sections were configured or found, so use root sections
            $baseSectionUids = self::getRootSectionUids();
        }

        // Create section objects
        $baseSections = array();
        foreach($baseSectionUids as $sectionUid) {
            $baseSections[] = new tx_newspaper_Section($sectionUid);
        }

        return $baseSections;
    }

    /**
     * Get all section uids regarding user TSConfig setting newspaper.baseSections
     * @static
     * @return array Section uids
     */
    public static function getBaseSectionTreeUids() {
        if (!empty(self::$base_section_uids)) return self::$base_section_uids;

        /** @var tx_newspaper_Section $baseSection */
        foreach(tx_newspaper_Section::getBaseSections() as $baseSection) {
            self::$base_section_uids[] = $baseSection->getUid();
            foreach($baseSection->getChildSections(true) as $section) {
                self::$base_section_uids[] = $section->getUid();
            }
        }
        return self::$base_section_uids;
    }
    private static $base_section_uids = array();

    static public function getModuleName() { return 'np_section'; }

    /**
     * Get root sections
     * @static
     * @return array Root sections
     */
    public static function getRootSections() {
        return array_map(
            function($uid) { return new tx_newspaper_Section($uid); },
            self::getRootSectionUids()
        );
    }

    /**
     * Get uids of root sections
     * @static
     * @return array Section uids
     */
    public static function getRootSectionUids() {
        return array_map(
            function(array $data) { return intval($data['uid']); },
            tx_newspaper::selectRows(
                'uid', 'tx_newspaper_section',
                'parent_section=0 AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Section()),
                '', 'sorting'
            )
        );
    }

    static public function getSectionForTypo3Page($typo_page_id) {

        $row = tx_newspaper::selectZeroOrOneRows(
            'tx_newspaper_associated_section', 'pages',
            'uid = ' . $typo_page_id
        );
        $section_uid = intval($row['tx_newspaper_associated_section']);

        if (!$section_uid) return null;

        return new tx_newspaper_Section($section_uid);

    }

    /**
     * Get a section rootline string
     * @param bool $includeSelf Whether or not the last section is included in the path
     * @param string $delimiter Delimiter for the sections in the rootline path
     * @return String $path Formatted section rootline
     */
    public function getFormattedRootline($includeSelf=true, $delimiter=' / ') {

        $rootLine = array_reverse($this->getSectionPath()); // Get complete rootline path
        if (!$includeSelf) array_pop($rootLine);

        return implode($delimiter, array_map(function(tx_newspaper_Section $s) { return $s->getSectionName(); }, $rootLine));

    }

    /**
     * itemsProcFunc to fill styles dropdowns in tceforms backend forms
     * @param Array $params Dropdown (called by reference!)
     * @param $pObj Parent object
     */
    public function addSectionsToDropdown(&$params, &$pObj) {
//tx_newspaper::devlog('addSectionsToDropdown', $params);
        $params['items'][] = array('', '');  // Start with an empty entry
        foreach (tx_newspaper_Section::getAllSections(false, 'section_name') as $section) {
            $params['items'][] = array(
                $section->getAttribute('section_name') . ' (' . $section->getFormattedRootline(false) . ')',
                $section->getUid()
            );
        }
    }

    /**
     * Get label for sections in backend (Typo3 TCA user function) (Section name and path in parentheses)
     * The label is set in $params (call by reference)
     * @param Array $params Data fetched by Typo3
     */
    public function getSectionBackendLabel(&$params) {
//t3lib_div::devlog('getSectionBackendLabel()', 'newspaper', 0, array('params' => $params));

        if (strpos($params['row']['uid'], 'NEW') !== false) {
            return ''; // New section in TCEForms, so no section record (so not section instance) yet
        }

        $section = new tx_newspaper_Section($params['row']['uid']);
        $path = $section->getFormattedRootline(false);
        $params['title'] = $section->getAttribute('section_name') . ($path? ' (' . $path . ')' : '');
    }

    /// Typo3  hooks

    /// \todo: documentation
    public static function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
        if ($status == 'new' && $table == 'tx_newspaper_section') {
            // a new section is stored, so assign a default article list
            $section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
            $s = new tx_newspaper_Section($section_uid);
            $s->assignDefaultArticleList();
        }
    }

    /// \todo: documentation
    public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
        if ($table != 'tx_newspaper_section') {
            return; // no section processed, nothing to do
        }

        // check if the article list was changed
        if (!isset($fieldArray['articlelist'])) {
            return; // article list wasn't changed, nothing to do
        }

//t3lib_div::devlog('al1 fiealdArray[al]', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id));
        if (tx_newspaper::isAbstractClass($fieldArray['articlelist']) || !class_exists($fieldArray['articlelist'])) {
            return; // well, ... can't create an object for an abstract or non-existing class
        }

        // note: the value in the backend dropdown is the name of the article list class ($fieldArray['articlelist'])

        if (new $fieldArray['articlelist']() instanceof tx_newspaper_articlelist) {
//t3lib_div::devlog('sh post in section', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id));
            // new article list class is a valid article list class, so change article list for this section now
            $s = new tx_newspaper_Section(intval($id)); // create section object
            $new_al = new $fieldArray['articlelist'](0, $s);
            if ($abstract_uid = $s->replaceArticleList($new_al)) {
                $fieldArray['articlelist'] = $abstract_uid; // store uid of abtracte article list in section, if replacing was successful
            }
        }
    }


     private $attributes = array();                    ///< The member variables
    private $subPages = array();
    private $articlelist = null;
    private $uid = 0;
    private $abstract_articlelist_id = 0;

     /// table which stores the tx_newspaper_ArticleList associated with this section
     static private $list_table = 'tx_newspaper_articlelist';
 }

tx_newspaper::registerSaveHook(new tx_newspaper_Section());

?>