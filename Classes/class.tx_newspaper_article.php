<?php

/**
 *  \file class.tx_newspaper_article.php
 *
 *  \author Lene Preuss <lene.preuss@gmx.net>
 *  \date Oct 27, 2008
 */
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');

require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_articlebehavior.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_tag.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_smarty.php');
require_once('private/class.tx_newspaper_articletextparagraphs.php');

/// An article for the online newspaper

/**
 *  The article is the central entity in a newspaper. Practically all other
 *  functionalities deal with displaying articles, lists of articles or
 *  additional information linked to articles.
 *
 *  Data-wise, an article consists of the minimum set of fields that every
 *  article must have. All additional data connected to an article (e.g. images,
 *  links, tags, media, ...) are called "Extra" and linked to an article. The
 *  class representing Extras is tx_newspaper_Extra and its descendants.
 *
 *  The Extras must be placed in an Article or in a PageZone.
 */
class tx_newspaper_Article extends tx_newspaper_PageZone implements tx_newspaper_ArticleIface, tx_newspaper_WritesLog {


    // @todo: Remove these 2 functions, see #2069
    public function getTazAutor() {
      $author = $this->getTazAutorArray();
      $authorString = '';
      for ($i = 0; $i < sizeof($author); $i++) {
        $authorString .= $author[$i]['name'];
        if ($i < (sizeof($author) - 2)) {
            $authorString .= ', ';
        } elseif ($i == (sizeof($author) - 2)) {
            $authorString .= ' und ';
        }
      }
      return $authorString;
    }
    public  function getTazAutorArray() {
        return tx_newspaper_DB::getInstance()->selectRows(
            '*',
            'tx_newspapertaz_tazid',
            'article_uid=' . $this->getUid(),
            '',
            'sorting'
        );
    }


    const article_related_table = 'tx_newspaper_article_related_mm';

    public static function createFromArray(array $data) {

        if (empty(self::$article_fields)) {
            self::$article_fields = tx_newspaper_DB::getInstance()->getFields('tx_newspaper_article');
        }

        $article = new tx_newspaper_Article();
        foreach (self::$article_fields as $key) {
            $article->attributes[$key] = $data[$key];
        }
        $article->setUid($data['uid']);

        return $article;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	magic methods ( http://php.net/manual/language.oop5.magic.php )
    //
    ////////////////////////////////////////////////////////////////////////////
    /// Create a tx_newspaper_Article

    /**
     *  Initializes the tx_newspaper_ArticleBehavior and tx_newspaper_Smarty
     *  used as auxiliaries.
     *
     *  Ensures that the current object has a record identifying it in the
     *  persistent storage as tx_newspaper_Extra and tx_newspaper_PageZone.
     */
    public function __construct($uid = 0, $lazy = false) {
        $this->articleBehavior = new tx_newspaper_ArticleBehavior($this);

        if ($uid) {
            $this->setUid($uid);
            if (!$lazy) {
                $this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());
                $this->pagezone_uid = $this->createPageZoneRecord();
            }
        }
    }

    ///	Things to do after an article is cloned
    /**
     *  This magic function is called after all attributes of a
     *  tx_newspaper_Article have been copied, when the PHP operation \p clone
     *  is executed on a tx_newspaper_Article.
     *
     * 	It ensures that all attributes are read from DB, \c crdate and \c tstamp
     *  are updated, and the new tx_newspaper_Article is written to DB. Also,
     *  the tx_newspaper_Extra objects associated with the tx_newspaper_Article
     *  are \p clone d.
     */
    public function __clone() {
        /*  ensure attributes are loaded from DB. readExtraItem() isn't
         *  called here because maybe the content is already there and it would
         *  cause the DB operation to be done twice.
         */
        $this->getAttribute('uid');

        //  unset the UID so the object can be written to a new DB record.
        $this->attributes['uid'] = 0;
        $this->setUid(0);

        $this->setAttribute('crdate', time());
        $this->setAttribute('tstamp', time());

        $this->store();

        /// clone extras, creating new abstract references to the concrete records
        $old_extras = $this->getExtras();
        $this->extras = array();
        foreach ($old_extras as $old_extra) {
            $this->extras[] = clone $old_extra;
        }
    }

    /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        $string = get_class($this) . ' ' . $this->getUid() . ' ' . "\n";
        if ($this->attributes) {
            $string .= 'attributes: ' . print_r($this->attributes, 1) . "\n";
        }
        if ($this->extras) {
            $string .= 'extras: ' . print_r($this->extras, 1) . "\n";
        }
        return $string;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	interface tx_newspaper_StoredObject
    //
    ////////////////////////////////////////////////////////////////////////////

    public function getAttribute($attribute) {

        if (!$this->attributes) {
            $this->attributes = tx_newspaper::selectOneRow(
                            '*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
            );
        }

        if (!array_key_exists($attribute, $this->attributes) && $this->getUid()) {
            throw new tx_newspaper_WrongAttributeException($attribute, $this->attributes, $this->getUid());
        }

        return $this->attributes[$attribute];
    }

    public function setAttribute($attribute, $value) {
        if (!$this->attributes) {
            $this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
        }

        $this->attributes[$attribute] = $value;
    }

    public function store() {
        $uid = $this->storeWithoutSavehooks();

        $this->callTypo3Savehooks(); // call Typo3 save hooks (triggers dependency tree etc.)

        return $uid;
    }

    /// insert article data (if uid == 0) or update if uid > 0
    public function storeWithoutSavehooks() {
        if ($this->getUid()) {
            /// If the attributes are not yet in memory, read them now
            if (!$this->attributes) {
                $this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
            }

            $this->setTypo3Attributes();

            tx_newspaper::updateRows(
                $this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
            );
        } else {
            $this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));

            $this->setTypo3Attributes();

            $this->setUid(
                tx_newspaper::insertRows(
                    $this->getTable(), $this->attributes
                )
            );
        }

        /// Ensure the page zone has an entry in the abstract supertable...
        $pagezone_uid = $this->createPageZoneRecord($this->getUid(), $this->getTable());

        $this->connectToPage($pagezone_uid);

        /// store all extras and make sure they are in the MM relation table
        if ($this->extras) {
            foreach ($this->extras as $extra) {
                $extra_uid = $extra->store();
                $extra_table = $extra->getTable();
                $this->relateExtra2Article($extra);
            }
        }

        return $this->getUid();

    }


    public function getUid() {
        if (!intval($this->uid))
            $this->uid = $this->attributes['uid'];
        return intval($this->uid);
    }

    public function setUid($uid) {
        $this->uid = $uid;
        if ($this->attributes) {
            $this->attributes['source_id'] = $uid;
            $this->attributes['uid'] = $uid;
        }
    }

    public function getTable() {
        return 'tx_newspaper_article';
    }

    static public function getModuleName() {
        return 'np_article';
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	interface tx_newspaper_ExtraIface
    //
    ////////////////////////////////////////////////////////////////////////////
    /// Renders an article with all of its Extras

    /** @param string $template_set Template set to use
     */
    public function render($template_set = '') {

        /// Default articles should never contain text that is displayed.
        if ($this->getAttribute('is_template')) return '';

        $timer = tx_newspaper_ExecutionTimer::create();

        $this->prepare_render($template_set);

        $paragraphs = new tx_newspaper_ArticleTextParagraphs($this);

        $this->assignSmartyVariables($paragraphs->toArray());

        // Add array for redirecting URL (if article is of type "Article as URL")
        $this->smarty->assign('redirectURL', tx_newspaper::getTypo3UrlArray($this->getAttribute('url')));

        $this->smarty->assign('typoscript', tx_newspaper::getNewspaperTyposcript());

        return $this->smarty->fetch($this);
    }

    protected function prepare_render(&$template_set = '') {
        $this->smarty = new tx_newspaper_Smarty();

        $this->setTemplateSet($template_set);

        $page = $this->getCurrentPage();
        $this->smarty->setPageType($page);

        $this->smarty->setPageZoneType($this);

        $this->smarty->assign('article', $this);

        $this->callRenderHooks();

    }

    /// Read data from table \p $table with UID \p $uid
    /**
     *  @param int $uid UID of the record to read
     *  @param string $table SQL table to read record from
     *  @return array The data contained in the requested record
     *  \todo remove.
     */
    static public function readExtraItem($uid, $table) {
        if (!$uid)
            return array();
        return tx_newspaper::selectOneRow('*', $table, 'uid=' . $uid);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	interface tx_newspaper_WithSource
    //
    ////////////////////////////////////////////////////////////////////////////

    public function getSource() {
        return $this->source;
    }

    public function setSource(array $source) {
        $this->source = $source;
        foreach ($source as $part) {
            if ($part instanceof tx_newspaper_Source) {
                $this->setAttribute('source_object', serialize($part));
            } else if ($part instanceof tx_newspaper_SourcePath) {
                $this->setAttribute('source_id', serialize($part));
            }
        }
    }

    public function setOriginUid() {}

    static public function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
        return tx_newspaper_ArticleBehavior::mapFieldToSourceField($fieldname, $source,
                self::$mapFieldsToSourceFields);
    }

    static public function addField($fieldname, $source_fieldname, tx_newspaper_Source $source) {
        if (!in_array($fieldname, self::$attribute_list)) {
            self::$attribute_list[] = $fieldname;
            self::$mapFieldsToSourceFields[get_class($source)][$fieldname] = $source_fieldname;
        }
    }

    static public function sourceTable(tx_newspaper_Source $source) {
        return tx_newspaper_ArticleBehavior::sourceTable($source, self::$table);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	interface tx_newspaper_ArticleIface
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     *  Get the list of tx_newspaper_Extra associated with this Article in sorted order.
     *
     *  The Extras are sorted by attribute \c paragraph first and \c position second.
     *
     *  @return tx_newspaper_Extra[] list of extras associated with this Article in sorted order
     */
    public function getExtras() {
        if (!$this->extras) {
            $extras = tx_newspaper::selectRows(
                            'uid_foreign', 'tx_newspaper_article_extras_mm',
                            'uid_local = ' . $this->getUid());
            if ($extras)
                foreach ($extras as $extra) {
                    try {
                        $show = tx_newspaper::selectOneRow('show_extra',
                                        'tx_newspaper_extra',
                                        'uid = ' . $extra['uid_foreign']);
                        if (TYPO3_MODE != 'BE' && !$show['show_extra'])
                            continue;

                        $new_extra = tx_newspaper_Extra_Factory::getInstance()->create($extra['uid_foreign']);
                        $this->extras[] = $new_extra;
                    } catch (tx_newspaper_EmptyResultException $e) {
                        /// Remove mm-table entry if the extra pointed to doesn't exist
                        $query = $GLOBALS['TYPO3_DB']->DELETEquery(
                                        'tx_newspaper_article_extras_mm', 'uid_foreign = ' . intval($extra['uid_foreign']));
                        $GLOBALS['TYPO3_DB']->sql_query($query);
                    }
                }
        }

        usort($this->extras, array(get_class($this), 'compareExtras'));

        return $this->extras;
    }

    /// Get article type of article
    /**
     *  @return tx_newspaper_ArticleType assigned to this Article
     */
    public function getArticleType() {
        return new tx_newspaper_ArticleType($this->getAttribute('articletype_id'));
    }

    /// Get a list of all attributes in the tx_newspaper_Article table.
    /** \return All attributes in the tx_newspaper_Article table.
     *  \todo A static attribute list sucks. Determine it dynamically.
     */
    static public function getAttributeList() {
        return self::$attribute_list;
    }

    /// Write record in MM table relating an Extra to this article
    /**
     *  The MM table record is only written if it did not exist beforehand.
     *
     *  If \p $extra did not have a record in the abstract Extra table
     *  (\c tx_newspaper_extra ), the record is created.
     *
     *  The MM-table \c tx_newspaper_article_extra_mm will contain an
     *  association between the Article's UID and the UID in the abstract Extra
     *  table.
     *
     *  @param $extra tx_newspaper_Extra The extra to add to \c $this.
     *
     *  @return int The UID of \p $extra in the abstract Extra table.
     */
    public function relateExtra2Article(tx_newspaper_ExtraIface $extra) {

        $extra_table = tx_newspaper::getTable($extra);
        $extra_uid = $extra->getUid();
        $article_uid = $this->getUid();

        $abstract_uid = $extra->getExtraUid();
        if (!$abstract_uid)
            $abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $extra_table);

        /// Write entry in MM table (if not exists)
        $row = tx_newspaper::selectZeroOrOneRows(
                        'uid_local, uid_foreign',
                        tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
                        'uid_local = ' . intval($article_uid) .
                        ' AND uid_foreign = ' . intval($abstract_uid)
        );

        if ($row['uid_local'] != $article_uid ||
                $row['uid_foreign'] != $abstract_uid) {
            if (!tx_newspaper::insertRows(
                            tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
                            array(
                                'uid_local' => $article_uid,
                                'uid_foreign' => $abstract_uid)
                    )
            ) {
                return false;
            }
        }

        return $abstract_uid;
    }


    ////////////////////////////////////////////////////////////////////////////
    //
    //	class tx_newspaper_PageZone
    //
    ////////////////////////////////////////////////////////////////////////////

    /// Add an extra after the Extra which is on the original page zone as $origin_uid
    /**
     *  Reimplemented from tx_newspaper_PageZone because concrete Articles don't
     *  have PageZones which inherit from them; default article are to be
     *  treated like PageZones. Setting \p $recursive on an concrete Article
     *  would result in an error.
     */
    public function insertExtraAfter(tx_newspaper_Extra $insert_extra, $origin_uid = 0, $recursive = true) {
        tx_newspaper_PageZone::insertExtraAfter($insert_extra, $origin_uid, $this->isDefaultArticle());
    }

    /// Get the tx_newspaper_PageZoneType associated with this Article
    /**
     *  @return tx_newspaper_PageZoneType The PageZoneType associated with this Article. If
     * 		this is not the one where attribute \p is_article is set, there
     * 		is something weird going on.
     *  \todo Check for \p is_article. No idea how to handle errors though.
     */
    public function getPageZoneType() {
        if (!$this->pagezonetype) {
            $pzt = tx_newspaper::selectOneRow('uid', 'tx_newspaper_pagezonetype', 'is_article');
            $pagezonetype_id = $pzt['uid'];
            $this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
        }
        return $this->pagezonetype;
    }

    public function getParentPage() {
        return $this->getCurrentPage();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	class tx_newspaper_Article
    //
    ////////////////////////////////////////////////////////////////////////////

    public function getExtrasOf($extra_class) {

        if ($extra_class instanceof tx_newspaper_Extra) {
            $extra_class = tx_newspaper::getTable($extra_class);
        }

        $extras = array();

        foreach ($this->getExtras() as $extra) {
            if (tx_newspaper::getTable($extra) == strtolower($extra_class)) {
                $extras[] = $extra;
            }
        }

        return $extras;
    }

    /// Find the first tx_newspaper_Extra of a given type
    /**
     *  @param $extra_class The desired type of tx_newspaper_Extra, either as
     *  	object or as class name
     *  @return tx_newspaper_Extra The first extra of the given class (by appearance
     * 		in article), or \c null.
     */
    public function getFirstExtraOf($extra_class) {
        $extras = $this->getExtrasOf($extra_class);
        if (sizeof($extras) > 0)
            return $extras[0];
        return null;
    }

    /// checks if article is a default article or a concrete article
    /**
     *  @return bool \c true if article is a default article (else \c false).
     */
    public function isDefaultArticle() {
        return $this->getAttribute('is_template');
    }

    /// Delete all Extras
    public function clearExtras() {
        $this->extras = array();
    }

    /// Change the paragraph of an Extra recursively on inheriting Articles
    /**
     *  This function is used to change the paragraph of an Extra on Articles
     *  that serve as templates for the placement of Articles in Sections
     *  ("Default Articles"). Because the \c paragraph attribute must be
     *  changed in the default articles of Sections that inherit from the
     *  current Section, this operation is non-trivial and cannot be performed
     *  by a simple setAttribute().
     *
     *  \p $extra is moved to the first position in the new paragraph, because
     * 	otherwise the operation would result in a random position. Extras
     *  already present in the target paragraph might have to be moved. That
     *  adds further complications to this function.
     *
     *  \todo Replace with a generic function to set attributes recursively.
     *
     *  @param tx_newspaper_Extra $extra Extra which should be moved to another
     * 		paragraph.
     *  @param int $new_paragraph The paragraph to which \p $extra is moved.
     */
    public function changeExtraParagraph(tx_newspaper_Extra $extra, $new_paragraph) {
// \todo: the changed paragraph is STORED in the extra but NOT MODIFIED in this pagezone's extras attribute
        $paragraph = intval($extra->getAttribute('paragraph'));
        if ($paragraph != intval($new_paragraph)) {
            $extra->setAttribute('paragraph', intval($new_paragraph));
            $extra->setAttribute('position', $this->getInsertPosition(0));
            $extra->store();

            /** Change the paragraph in inheriting page zones too.
             *  \todo Optional: only overwrite paragraph in inheriting pagezones
             *  if it has not been changed manually there.
             */
            foreach ($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
                if (!($inheriting_pagezone instanceof tx_newspaper_Article)) {
                    /* Probably no harm can come if the page zone is not an
                     * Article. So we just write a message to the devlog and
                     * skip it.
                     */
                    t3lib_div::devlog(
                                    'Weird: There\'s a PageZone inheriting from an Article which is not itself an Article',
                                    'newspaper', 0,
                                    array(
                                        'parent page zone' => $this,
                                        'inheriting page zone' => $inheriting_pagezone
                                    )
                    );
                    continue;
                }
                $copied_extra = $inheriting_pagezone->findExtraByOriginUID($extra->getOriginUid());
                if ($copied_extra)
                    $inheriting_pagezone->changeExtraParagraph($copied_extra, $new_paragraph);
            }
        }
    }

    /// Generates a URL which links to the tx_newspaper_Article on the correct tx_newspaper_Page.
    /**
     *  @param $section Section from which the link is generated. Defaults to
     * 		using the article's primary section.
     *  @param $pagetype tx_newspaper_PageType of the wanted tx_newspaper_Page.
     *  @todo Handle PageType other than article page.
     *  @todo Check if target page has an Article display Extra, tx_newspaper_Extra_DisplayArticles.
     */
    public function getLink(tx_newspaper_Section $section = null,
                            tx_newspaper_PageType $pagetype = null,
                            array $additional_parameters = array()) {

        if ($this->isArticleTypeUrl()) {
            // Article type "Article as URL": Simply return URL.
            // If more data is needed (CSS target, class, title), this data can be retrieved
            // in the template by accessing $article.redirectURL.[href|target|css|title]
            $data = tx_newspaper::getTypo3UrlArray($this->getAttribute('url'));
            return $data['href'];
        }

        $section = $this->determineRelevantSection($section);
        $typo3page = $section->getTypo3PageID();

        $get_vars = array(
            'id' => $typo3page,
            tx_newspaper::article_get_parameter => $this->getUid()
        );
        $get_vars = array_merge($get_vars, $additional_parameters);
        $get_vars = array_unique($get_vars);

        return tx_newspaper::typolink_url($get_vars);
    }

    private function determineRelevantSection(tx_newspaper_Section $section = null) {
        if (!$section) {
            $section = $this->getPrimarySection();
        }

        if (!$section instanceof tx_newspaper_Section) {
            //	find section at the root of the section tree
            //	uses the first section without a parent section
            $section_data = tx_newspaper::selectOneRow(
                            'uid', tx_newspaper::getTable('tx_newspaper_Section'),
                            'NOT parent_section', '', 'uid'
            );
            $section = new tx_newspaper_Section($section_data['uid']);
        }

        return $section;
    }

    /// Gets a list of tx_newspaper_Article objects assigned to given article type
    /**
     *  \param $at tx_newspaper_ArticleType object
     *  \param $limit max number of records to read (default: 10), if negative no limit is used
     *  \return array with tx_newspaper_Article objects
     */
    static public function listArticlesWithArticletype(
    tx_newspaper_ArticleType $at, $limit = 10
    ) {

        $limit = intval($limit);
        $limit_part = ($limit > 0) ? '0,' . $limit : '';

        $row = tx_newspaper::selectRows(
                        'uid',
                        'tx_newspaper_article',
                        'deleted=0 AND articletype_id=' . $at->getUid(),
                        '',
                        'tstamp DESC',
                        $limit_part
        );

        $list = array();
        for ($i = 0; $i < sizeof($row); $i++) {
            $list[] = new tx_newspaper_Article($row[$i]['uid']);
        }
        return $list;
    }

    /// Adds a tx_newspaper_Section to the tx_newspaper_Article.
    /**
     *  The new tx_newspaper_Section will be inserted after existing sections.
     *  The Article is listed in tx_newspaper_Section \p $s afterwards.
     *
     *  \param $s New Section
     */
    public function addSection(tx_newspaper_Section $s) {
/// \todo: if ($this->getUid() == 0) throw e OR
/// \todo: just collect here and store sections later in article::store()
        // get pos of next element
        $p = tx_newspaper_DB::getInstance()->getLastPosInMmTable('tx_newspaper_article_sections_mm', $this->getUid()) + 1;

        tx_newspaper::insertRows(
                        'tx_newspaper_article_sections_mm',
                        array(
                            'uid_local' => $this->getUid(),
                            'uid_foreign' => $s->getUid(),
                            'sorting' => $p
                        )
        );
    }

    ///	Sets the sections of an article to exactly the input sections
    /**
     *  \param $uids UIDs of the tx_newspaper_Section s which \c $this will
     *  	belong to.
     */
    public function setSections(array $uids) {
//t3lib_div::devlog('setSections()', 'newspaper', 0, array($uids));
        // 	Ensure that it's reasonably safe to delete Article-Section relations
        foreach ($uids as $uid) {
            if (!$uid instanceof tx_newspaper_Section) {
                if (!intval($uid)) {
                    throw new tx_newspaper_IllegalUsageException('Section UID is not an integer');
                }
            }
        }

        tx_newspaper::deleteRows(
                        'tx_newspaper_article_sections_mm',
                        'uid_local = ' . $this->getUid()
        );

        foreach ($uids as $uid) {
            if (!$uid instanceof tx_newspaper_Section) {
                $uid = new tx_newspaper_Section($uid);
            }
            $this->addSection($uid);
        }
    }

    /**
     *  Get the list of tx_newspaper_Section s to which the current article belongs
     *
     *  @param $limit Maximum number of tx_newspaper_Section s to find
     *  @param $sorted If set, the sections gets sorted level-wise
     *  @return tx_newspaper_Section[] to which the current article belongs
     */
    public function getSections($limit = 0, $sorted=false) {
        $section_ids = tx_newspaper::selectRows(
                        'uid_foreign',
                        'tx_newspaper_article_sections_mm',
                        'uid_local = ' . $this->getUid(),
                        '',
                        'sorting',
                        $limit ? "0, $limit" : ''
        );

        $sections = array();
        foreach ($section_ids as $id) {
            $sections[] = new tx_newspaper_Section($id['uid_foreign']);
        }
        return $sections;
    }

    /**
     *  Get the primary tx_newspaper_Section of a tx_newspaper_Article.
     *
     *  @return tx_newspaper_Section in which \p $this is displayed by default, if no Section context is given
     */
    public function getPrimarySection() {
        $sections = $this->getSections(1);
        if (sizeof($sections)) return $sections[0];
        return new tx_newspaper_NullSection();
    }

    /** @return bool Whether the article is in section $section. */
    public function hasSection(tx_newspaper_Section $section) {
        foreach ($this->getSections() as $check_section) {
            if ($check_section->getUid() == $section->getUid()) return true;
        }
        return false;
    }

    /**
     *  Gets a list of (configured but) missing extras in the article
     *
     *  It is checked if extras placed on the default article are missing in
     *  the concrete article and if extras configured as must-have or should-
     *  have are missing in the article.
     *  @return tx_newspaper_Extra[] either existing extras or newly created empty extras
     */
    public function getMissingDefaultExtras() {

        $shortcuts = array();

        // get must-have/should-have configuration
        try {
            $at = $this->getArticleType();
            $at->getAttribute('uid'); // access article type to force database access
        } catch (tx_newspaper_EmptyResultException $e) {
            // article type could not be found - either article type is 0 or article type is deleted for some reason
            t3lib_div::devlog('getMissingDefaultExtras(): no article type set for article #' . $this->getUid(), 'newspaper', 3);
            return array(); // no article type, no shortcuts ...
        }

        $must_should_have_extras = array();

        $tsc_extras = array_merge($at->getTSConfigSettings('musthave'), $at->getTSConfigSettings('shouldhave'));
        foreach ($tsc_extras as $tsc_extra) {
//t3lib_div::devlog('getMissingDefaultExtras()', 'newspaper', 0, array('e' => $tsc_extra));
            list($extra_class, $paragraph) = explode(':', $tsc_extra);
            $paragraph = intval($paragraph);

            if (tx_newspaper::classImplementsInterface($extra_class, 'tx_newspaper_ExtraIface')) {
                if (!$this->checkExtra($extra_class)) {
                    /** @var $e tx_newspaper_Extra */
                    $e = new $extra_class();
                    $shortcuts[] = array(
                        'extra_class' => $extra_class,
                        'paragraph' => $paragraph,
                        'title' => $e->getTitle(),
                    );
                }
            } // \todo: log errors?
        }

        // here was a very long, commented-out section. if you need it, retrieve it from svn rev. 9041.

        return $shortcuts;
    }

    /// Get the SQL table which associates tx_newspaper_Extra with tx_newspaper_PageZone.
    public function getExtra2PagezoneTable() {
        return self::$extra_2_pagezone_table;
    }

    /// Is the current article an "Article as URL"
    /**
     * Checks if current article type is configured as "Article as URL" in newspaper.articleTypeAsUrl = [uid1,...uidn]
     * @return bool True if article is configured to be an "Article as URL", else false
     */
    public function isArticleTypeUrl() {
        $tsc = tx_newspaper::getTSConfig();
        if ($articleType = t3lib_div::trimExplode(',', $tsc['newspaper.']['articleTypeAsUrl'])) {
            return in_array($this->getArticleType()->getAttribute('uid'), $articleType);
        }
        // no TSConfig found, so always false
        return false;
    }

	/// Attach a tag to the article
	/**
	 * \param $newTag Tag (either content or control tag)
	 * \return true if tag was assigned, false if not (because the tag has been assigned already)
	 */
    public function attachTag(tx_newspaper_tag $newTag) {

    	// read tags (of same type as the tag to be attached right now) that are already attached to this article
		$tags = $this->getTags($newTag->getAttribute('tag_type'));
		foreach($tags as $tag) {
			if ($tag->getUid() == $newTag->getUid()) {
				return false; // tag has been attached already
			}
		}

		// calculate position of tag to be attached
		$tags = $this->getTags(tx_newspaper_tag::getContentTagType()); // read content tags
		$newPosition = sizeof($tags);
    	$tags = $this->getTags(tx_newspaper_tag::getControlTagType()); // read control tags
    	$newPosition += sizeof($tags) + 1; // new pos: behind all other tags attached to this article

    	// attach tag
		tx_newspaper::insertRows('tx_newspaper_article_tags_mm', array(
			'uid_local' => $this->getUid(),
			'uid_foreign' => $newTag->getUid(),
			'sorting' => $newPosition
		));

		$this->callTypo3Savehooks();

		return true; // a new tag was attached to the article
    }

    /**
     * Detach $tag from this article
     * @param $tag
     * @return Number of records processed
     */
    public function detachTag(tx_newspaper_tag $tag) {
    	// detach tag from article
    	$count = tx_newspaper::deleteRows(
    		'tx_newspaper_article_tags_mm',
    		$this->getUid(),
			'uid_local',
			'uid_foreign=' . $tag->getUid()
		);

		$this->callTypo3Savehooks();

		return $count;
    }

    /**
     * \param  $tagtype int defaults to contentTagType
     * @return tx_newspaper_Tag[] array with tags objects
     */
    public function getTags($tagtype = null, $category =  null) {
        $where = " AND uid_local = " . $this->getUid();

        if($tagtype) {
            $where .= " AND tag_type = " . $tagtype;
        }
        if($category) {
            $where .= " AND ctrltag_cat = " . intval($category);
        }

        $tag_ids = tx_newspaper_DB::getInstance()->selectMMQuery('uid_foreign', $this->getTable(),
            'tx_newspaper_article_tags_mm', 'tx_newspaper_tag', $where);

		$tags = array();
		foreach ($tag_ids as $id) {
			$tags[] = new tx_newspaper_Tag($id['uid_foreign']);
		}
		return $tags;
    }

    /** @return tx_newspaper_Article[] */
    public function getRelatedArticles($hidden_ones_too = false) {

        $rows = tx_newspaper::selectRowsDirect(
                        self::article_related_table . '.uid_local, ' . self::article_related_table . '.uid_foreign',
                        self::article_related_table .
                        ' JOIN ' . $this->getTable() . ' AS a_local' .
                        ' ON ' . self::article_related_table . '.uid_local = a_local.uid' .
                        ' JOIN ' . $this->getTable() . ' AS a_foreign' .
                        ' ON ' . self::article_related_table . '.uid_foreign= a_foreign.uid',
                        '(uid_local = ' . $this->getUid() .
                        ' OR uid_foreign = ' . $this->getUid() . ')' .
                        self::fakeEnableFieldsForRelated($hidden_ones_too)
        );

        $related_articles = array();
        foreach($this->getRelatedArticleUids($rows) as $uid) {
            $related_articles[] = new tx_newspaper_Article($uid);
        }
        return $related_articles;
    }

    private function getRelatedArticleUids($rows) {
        $related_uids = array();

        foreach ($rows as $row) {
            $this->addArticleUID($related_uids, $row, 'uid_local', 'uid_foreign');
            $this->addArticleUID($related_uids, $row, 'uid_foreign', 'uid_local');
        }

        return array_unique($related_uids);
    }

    private function addArticleUID(array &$related, array $row, $local, $foreign) {
        if (intval($row[$local]) == $this->getUid()) {
            if (intval($row[$foreign]) != $this->getUid()) {
                $related[] = intval($row[$foreign]);
            }
        }
    }

    private static function fakeEnableFieldsForRelated($hidden_ones_too) {
        if ($hidden_ones_too) return '';
        return 'AND (a_local.hidden = 0)' . ' AND (a_foreign.hidden = 0)' .
            self::getTimeWhereClause('a_local') . self::getTimeWhereClause('a_foreign');
    }

    private static function getTimeWhereClause($table) {
        return " AND ( $table.starttime <= " . time() . " AND ( $table.endtime = 0 OR $table.endtime > " . time() . '))';
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	Typo3 hooks
    //
    ////////////////////////////////////////////////////////////////////////////

    /**
     *  - remembers which control tags were present \em before the article was saved
     *  - merges all types of tags into one field
     *  \param $incomingFieldArray The values sent to the save hook via POST
     *  \param $table The SQL table for which this record was saved
     *  \param $id UID of this record
     *  \param $that The t3lib_tcemain object handling the datamap that stores the record
     */
    public static function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
        if (!self::isValidForSavehook($table, $id)) return;

        self::saveOldControlTagsForArticle($id);

        self::joinTags($incomingFieldArray, $table, $id, $that);
    }

    private static function isValidForSavehook($table, $id) {
        return (
            strtolower($table) == 'tx_newspaper_article' &&
            intval($id) > 0 &&
            tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', "uid = $id")
        );
    }

    private static function saveOldControlTagsForArticle($article_uid) {
        $article_before_db_ops = self::safelyInstantiateArticle($article_uid);
        if (!$article_before_db_ops instanceof tx_newspaper_Article) return;

        self::$tags_before_db_ops = $article_before_db_ops->getTags(tx_newspaper_Tag::getControlTagType());
    }
    private static $tags_before_db_ops = array();

    private static function safelyInstantiateArticle($id) {
        $article = new tx_newspaper_Article(intval($id));

        try {
            $article->getAttribute('uid');
        } catch (tx_newspaper_Exception $e) {
            return null;
        }

        return $article;
    }

    public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
//tx_newspaper_Debug::w('tx_newspaper_article::processDatamap_postProcessFieldArray', tx_newspaper_taz_Savehooks::deptree_debug_log);

        if (!self::isValidForSavehook($table, $id)) return;

        self::addPublishDateIfNotSet($id, $fieldArray); // check if publish_date is to be added

        $article = self::safelyInstantiateArticle($id);
        if (!$article instanceof tx_newspaper_Article) return;

        $article->ensureRelatedArticlesAreBidirectional();
        $article->removeDanglingRelations();
        $article->checkSectionIsValid();

        self::updateDependencyTree($article, $fieldArray);

    }

    private static function getRemovedTags(tx_newspaper_Article $article) {
        $tags_after_db_ops = $article->getTags(tx_newspaper_Tag::getControlTagType());

        $removed_tags = array_diff(self::$tags_before_db_ops, $tags_after_db_ops);

        self::$tags_before_db_ops = array();

        return $removed_tags;
    }


    /**
     * Typo3 hook: TCEForms is about to render a single form field
     * @param $table
     * @param $field
     * @param $row
     * @param $altName
     * @param $palette
     * @param $extra
     * @param $pal
     * @param $pObj
     */
    public static function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $pObj) {
        self::modifyTagSelection($table, $field);
        self::processTSConfig($table, $field, $row);
    }

    public static function getSingleField_postProcess($table, $field, $row, &$out, $PA, $that) {

    }

    /**
     * Stuff to do when an article gets deleted:
     * 1. Call DepTree, if the article gets deleted
     * @static
     * @todo: documentation, copy from T3 doc ...
     * @param $command
     * @param $table
     * @param $id
     * @param $value
     * @param $pObj
     */
    public static function processCmdmap_preProcess($command, $table, $id, $value, $pObj) {
        $id = intval($id);
        if ($command == 'delete' && $table == 'tx_newspaper_article' && $id) {
            self::updateDependencyTree(new tx_newspaper_Article($id));
        }
    }

    public static function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
        if (strtolower($table) == 'tx_newspaper_article') {
            // hide default articles in list module, only concrete article are visible in list module
            $additionalWhereClause .= ' AND is_template=0';
        }
    }

    /** This function uses Typo3 datamap functionality to assure Typo3 save hooks are called,
     *  so registered Hooks in newspaper are called too.
     *  This function writes the hidden status into the database immediately.
     *  \param $uid article uid
     *  \param $hidden boolean value specifying if the article is hidden or published
     *  \todo: replace with newspaper hook handling, see #1055.
     */
    public function storeHiddenStatusWithHooks($hidden) {

        $timer = tx_newspaper_ExecutionTimer::create();

        $this->storeHiddenStatusWithHooksComplicated((bool)$hidden);

    }

    public function storeHiddenStatusWithHooksComplicated($hidden_value)
    { // prepare datamap
        $datamap['tx_newspaper_article'][$this->getUid()] = array(
            'hidden' => $hidden_value,
            'tstamp' => time(),
            'modification_user' => tx_newspaper::getBeUserUid()
        );

        // use datamap, so all save hooks get called
        /** @var $tce t3lib_TCEmain */
        $tce = t3lib_div::makeInstance('t3lib_TCEmain');
        $tce->start($datamap, array());
        $tce->process_datamap();
        if (count($tce->errorLog)) {
            throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
        }

        // store in object
        $this->setAttribute('hidden', $hidden_value);

        if (!$hidden_value) {
            // if article is published the publish date might have been stored in save hook, so re-read it from db
            $articleFromDb = new tx_newspaper_article($this->getUid());
            $this->setAttribute('publish_date', $articleFromDb->getAttribute('publish_date'));
            $this->setAttribute('tx_newspapertaz_vgwort_public_id', $articleFromDb->getAttribute('tx_newspapertaz_vgwort_public_id'));
        }

        // \todo is this really needed? find out why or remove it
        // \todo if it is needed: why not storeWithoutSavehooks()? using store() calls the savehook twice.
        $this->store();
    }

    /// Registers a hook that is called during prepare_render()
    /** \param $class Name of the class which defines the render-hook as static method
     *  \param $function Static method of \p $class, which takes two arguments: the article
     *      object and the article's Smarty member.
     *  \attention PHP 5.0.x does not include the static methods when checking with
     *      \c method_exists(). Therefore, this function will not with a PHP version prior
     *      to 5.1.
     *  \attention It seems I cannot check for the number of arguments to $function.
     *      I cannot make sure that \p $function takes the correct arguments. The caller
     *      must ensure that function takes a \c tx_newspaper_Article and a \c tx_newspaper_Smarty.
     */
    public static function registerRenderHook($class, $function) {
        if (!class_exists($class)) return;
        if (!method_exists($class, $function)) return;
        self::$render_hooks[$class] = $function;
    }

    /**
     * Register additional attribute
     * @param string $attribute KEy for additional attribute. NO check if attribute key is available
     * @param string $class Class name
     * @param string $function Function name
     */
    public static function registerAdditionalAttribute($attribute, $class, $function) {
        if (!class_exists($class) || !method_exists($class, $function)) {
            return;
        }
        self::$additionalAttributeConfig[$attribute] = array($class => $function);
    }

    /**
     * Get value of additional attribute register with tx_newspaper_article::registerAdditionalAttribute()
     * @param string $key Key for additional registered attribute
     * @return mixed Whatever the registered function returns, or null if $key is not registered
     */
    public function getAdditionalAttribute($key) {
        if (!isset(self::$additionalAttributeConfig[$key]) && !is_array(self::$additionalAttributeConfig[$key])) {
            return null;
        }

        $class = key(self::$additionalAttributeConfig[$key]);
        $function = self::$additionalAttributeConfig[$key][$class];

        $object_to_call_static_method_on = new $class();
        return $object_to_call_static_method_on->$function($this); // get value for additional attribute
    }


    public static function updateDependencyTree(tx_newspaper_Article $article, array $field_array = array()) {
        if (tx_newspaper_DependencyTree::useDependencyTree()) {
tx_newspaper_Debug::w('tx_newspaper_article::updateDependencyTree', tx_newspaper_taz_Savehooks::deptree_debug_log);
            $tags = self::getRemovedTags($article);
            $tree = tx_newspaper_DependencyTree::generateFromArticle($article, $tags, $field_array);
            $tree_proxy = new tx_newspaper_DependencyTreeProxy($tree);
            $tree_proxy->executeActionsOnPages('tx_newspaper_Article');
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //	protected functions
    //
    ////////////////////////////////////////////////////////////////////////////
    /// Find out which tx_newspaper_Page is currently displayed

    /** Uses \c $_GET to find out which tx_newspaper_PageType is requested on
     *  the current tx_newspaper_Section.
     *
     *  \return The currently displayed tx_newspaper_Page.
     *
     *  \todo make static, move to tx_newspaper
     */
    protected function getCurrentPage() {
        if (TYPO3_MODE == 'BE') {
            $section = $this->getPrimarySection();
        } else {
            $section = tx_newspaper::getSection();
        }
        $pagetype = new tx_newspaper_PageType($_GET);

        return new tx_newspaper_Page($section, $pagetype);
    }

    /// Get the index of the provided tx_newspaper_Extra in the Extra array
    /** Binary search for an Extra, assuming that \c $this->extras is ordered by
     *  paragraph first and position second.
     *
     *  \param $extra tx_newspaper_Extra to find
     *  \return Index of \p $extra in \c $this->extras
     *  \throw tx_newspaper_InconsistencyException if \p $extra is not present
     * 		in \c $this->extras
     */
    protected function indexOfExtra(tx_newspaper_Extra $extra) {
        $high = sizeof($this->getExtras()) - 1;
        $low = 0;

        while ($high >= $low) {
            $index_to_check = floor(($high + $low) / 2);
            $comparison = $this->getExtra($index_to_check)->getAttribute('paragraph') -
                    $extra->getAttribute('paragraph');
            if ($comparison < 0)
                $low = $index_to_check + 1;
            elseif ($comparison > 0)
                $high = $index_to_check - 1;
            else {
                $comparison = $this->getExtra($index_to_check)->getAttribute('position') -
                        $extra->getAttribute('position');
                if ($comparison < 0)
                    $low = $index_to_check + 1;
                elseif ($comparison > 0)
                    $high = $index_to_check - 1;
                else
                    return $index_to_check;
            }
        }

        // Loop ended without a match
        tx_newspaper::devlog(
            'Extra ' . $extra->getExtraUid() . '(' . $extra->getTable() . ' ' . $extra->getUid() .
                ') not found in array of Extras for article ' . $this->getUid(),
            array('article' => $this, 'extra' => $extra),
            'newspaper',
            1
        );

        return sizeof($this->getExtras())-1;
    }

    /// Ordering function to keep Extras in the same order as they appear on the PageZone
    /** Supplied as parameter to \c usort() in getExtras().
     *  \param $extra1 first tx_newspaper_Extra to compare
     *  \param $extra2 second tx_newspaper_Extra to compare
     *  \return < 0 if \p $extra1 comes before \p $extra2, > 0 if it comes after,
     * 			== 0 if their position is the same
     */
    static protected function compareExtras(tx_newspaper_ExtraIface $extra1, tx_newspaper_ExtraIface $extra2) {
        $paragraph1 = $extra1->getAttribute('paragraph');
        $paragraph2 = $extra2->getAttribute('paragraph');

        if ($paragraph1 == $paragraph2) {
            return $extra1->getAttribute('position') - $extra2->getAttribute('position');
        }

		// paragraph 0 is always the first element ...
        if ($paragraph1 == 0) {
        	return -1;
        }
        if ($paragraph2 == 0) {
        	return 1;
        }

        /** 	Negative paragraphs are sorted at the end, in reverse order.
         *  So, a negative paragraph comes AFTER a positive paragraph. If both
         *  are negative though, it's the usual order: The smaller value (higher
         *  negative value) comes first.
         */
        if ($paragraph1 * $paragraph2 < 0)
            return $paragraph2 - $paragraph1;
        else
            return $paragraph1 - $paragraph2;
    }

    /// SQL table which associates tx_newspaper_Extra s with tx_newspaper_PageZone s
    static protected $extra_2_pagezone_table = 'tx_newspaper_article_extras_mm';

    ////////////////////////////////////////////////////////////////////////////
    //
    //	private functions
    //
    ////////////////////////////////////////////////////////////////////////////

    /** Check whether to use a specific template set.
     *  This must be done regardless if this is a template used to define
     *  default placements for articles, or an actual article.
     */
    private function setTemplateSet($template_set) {
        if ($this->getAttribute('template_set')) {
            $template_set = $this->getAttribute('template_set');
        }

        /// Configure Smarty rendering engine.
        if ($template_set) {
            $this->smarty->setTemplateSet($template_set);
        }
    }

    private function assignSmartyVariables(array $paragraphs) {
        $this->smarty->assign('paragraphs', $paragraphs);
        $this->smarty->assign('attributes', $this->attributes);
        $this->smarty->assign('extras', $this->getExtras());
        $this->smarty->assign('link', $this->getLink());

        $this->assignTags();
    }

    private function assignTags() {
        $tags = array();
        foreach ($this->getTags() as $tag) {
            $tags[] = array(
                'uid' => $tag->getUid(),
                'title' => $tag->getAttribute('title')? $tag->getAttribute('title'): $tag->getAttribute('tag'),
                'tag_type' => $tag->getAttribute('tag_type'),
                'ctrltag_cat_id' => $tag->getAttribute('ctrltag_cat'),
                'ctrltag_category' => $tag->getCategoryName()
            );
        }
        $this->smarty->assign('tags', $tags);
    }

    /// Checks if an extra type is assigned to this article. If a $paragraph is given, an extra is searched for on that paragraph.
    /** \param $class name of extra class
     *  \param $paragraph paragraph or false, if paragraph shouldn't be checked
     *  \return true if the extra was found, else false
     */
    private function checkExtra($class, $paragraph=false) {
        $class = strtolower($class);
        if ($paragraph !== false) {
            $paragraph = intval($paragraph);
        }
        foreach ($this->getExtras() as $extra) {
            if ($class == strtolower($extra->getTable())) {
                if ($paragraph !== false) {
                    return ($extra->getAttribute('paragraph') == $paragraph);
                } else {
                    return true;
                }
            }
        }
        return false;
    }


    /// Make sure that an article related to \c $this has also \c $this as relation.
    private function ensureRelatedArticlesAreBidirectional() {

        foreach ($this->getRelatedArticles(true) as $related_article) {
            $row = tx_newspaper::selectZeroOrOneRows(
                            'uid_local', self::article_related_table,
                            'uid_foreign = ' . $this->getUid() . ' AND uid_local = ' . $related_article->getUid());
            if ($row)
                continue;

            $relation_to_write = array(
                'uid_local' => $related_article->getUid(),
                'uid_foreign' => $this->getUid()
            );
            tx_newspaper::insertRows(self::article_related_table, $relation_to_write);
        }
    }

    private function removeDanglingRelations() {
        $rows = tx_newspaper::selectRows(
                        self::article_related_table . '.uid_foreign',
                        self::article_related_table,
                        'uid_local = ' . $this->getUid()
        );

        $uids = array(0);
        foreach ($rows as $article) {
            $uids[] = $article['uid_foreign'];
        }
//t3lib_div::devlog('removeDanglingRelations()', 'newspaper', 0, array('rows'=>$rows, 'uids'=>$uids));

        $where = 'uid_foreign = ' . $this->getUid() . ' AND uid_local NOT IN (' . implode(', ', $uids) . ')';
        $rows = tx_newspaper::deleteRows(
                        self::article_related_table,
                        $where
        );
    }

    private function checkSectionIsValid() {
        try {
            if ($section = $this->getPrimarySection()) {
            	$section->getTypo3PageID();
            } else {
	            tx_newspaper_Workflow::directLog(
	            	$this->getTable(),
	            	$this->getUid(),
	            	tx_newspaper::getTranslation('message_article_no_section'),
	            	NP_WORKLFOW_LOG_ERRROR
	            );
            }
        } catch (tx_newspaper_IllegalUsageException $e) {
            $msg = tx_newspaper::getTranslation('message_section_typo3page_missing');
            $msg = str_replace('###SECTION###', $this->getPrimarySection()->getAttribute('section_name'), $msg);
            tx_newspaper_Workflow::directLog($this->getTable(), $this->getUid(), $msg, NP_WORKLFOW_LOG_ERRROR);
        }
    }

    /// Set attributes used by Typo3
    private function setTypo3Attributes() {
        $this->setAttribute('tstamp', time());
    }

    private function callRenderHooks() {
        foreach (self::$render_hooks as $class => $method) {
            $object_to_call_static_method_on = new $class();
            $object_to_call_static_method_on->$method($this, $this->smarty);
        }
    }

    /**
     * Joins tags from control- and content-selectboxes so both are stored in a single table.
     * @static
     * @param  $incomingFieldArray
     * @param  $table
     * @param  $id
     * @param  $that
     * @return void
     */
    private static function joinTags(&$incomingFieldArray, $table, $id, $that) {
//t3lib_div::devlog('joinTags()', 'newspaper', 0, array('incommingFields' => $incomingFieldArray));
        if ($table == 'tx_newspaper_article' && isset($incomingFieldArray['tags'])) {
            $tags = $incomingFieldArray['tags'];
            $ctrlTags = array();

            foreach($incomingFieldArray as $field => $value) {
                if(stristr($field, 'tags_ctrl')) {
                    $ctrlTags = array_merge($ctrlTags, explode(',', $value));
                }
            }
            if (!empty($ctrlTags)) {
                $tags = explode(",", $tags);
                $allTags = implode(",", array_merge($tags, $ctrlTags));
                $incomingFieldArray['tags'] = $allTags;
                $_REQUEST['data'][$table][$id]['tags'] = $allTags;
            }
        }
    }

    private static function modifyTagSelection($table, $field) {
        if ('tx_newspaper_article' === $table && 'tags' === $field) {
//t3lib_div::devLog('modifyTagSelection()', 'newspaper', 0, array('table' => $table, 'field' => $field));
            global $TCA;
            $TCA['tx_newspaper_article']['columns']['tags']['config']['type'] = 'user';
            $TCA['tx_newspaper_article']['columns']['tags']['config']['userFunc'] = 'tx_newspaper_be->renderTagControlsInArticle';
        }
    }


    /**
     * Process TSConfig settings:
     * newspaper.be.[field].useRteForArticleTypes = [comma separated list of article type uids]
     * newspaper.be.[field].hideFieldForArticleTypes = [comma separated list of article type uids]
     * newspaper.be.[field].showFieldForArticleTypes = [comma separated list of article type uids]
     * @param string $table Must be set to 'tx_newspaper_article'
     * @param string $field Field to be processed in 'tx_newspaper_article'
     * @param array $row Current article data
     */
    private static function processTSConfig($table, $field, $row) {
        if ($table != 'tx_newspaper_article') {
            return; // Ignore all tables but tx_newspaper_article
        }

        $tscField = $field . '.'; // In TSConfig array the field name is appeding by a "."
        $articleTypeUid = $row['articletype_id']; // Article type uid stored in current article

        $tsc = tx_newspaper::getTSConfig(); // Read TSConfig
//t3lib_div::devlog('a process tsc', 'newspaper', 0, array($tsc['newspaper.']['be.'][$tscField]));

        if (self::checkTSConfigArticleTypesSetting($articleTypeUid, $tsc['newspaper.']['be.'][$tscField]['useRteForArticleTypes'])) {
            self::useRteInBackend($field); // Show RTE for field
        }

        if (self::checkTSConfigArticleTypesSetting($articleTypeUid, $tsc['newspaper.']['be.'][$tscField]['hideFieldForArticleTypes'])) {
            self::hideFieldInBackend($field); // Hide field in backend
        }

        if (self::checkTSConfigArticleTypesSetting($articleTypeUid, $tsc['newspaper.']['be.'][$tscField]['showFieldForArticleTypes'])) {
            // @todo: Show field in backend
            t3lib_div::devlog('newspaper.be.[field].showFieldForArticleTypes is not yet implemeted', 'newspaper', 2);
        }

    }

    /**
     * Hides fields in backend (the data stored in the field remians stored in the database)
     * @param string $field Field in article
     */
    private static function hideFieldInBackend($field) {
        unset($GLOBALS['TCA']['tx_newspaper_article']['columns'][$field]['config']);
        $GLOBALS['TCA']['tx_newspaper_article']['columns'][$field]['config']['type'] = 'passthrough';
    }

    /**
     * Manipulate TCA: Add a RTE to $field
     * IMPORTANT NOTE: The RTE usage MUST be prepared in TCA type for field, in order for this setting to work
     * @param string $field Field in article
     */
    private static function useRteInBackend($field) {
        // @todo: check if $field is a text field. Does it make sense to switch to RTE?
        unset($GLOBALS['TCA']['tx_newspaper_article']['columns'][$field]['config']);
        $GLOBALS['TCA']['tx_newspaper_article']['columns'][$field]['config'] = array(
            "type" => "text",
            "cols" => "30",
            "rows" => "5",
            "wizards" => Array(
                "_PADDING" => 2,
                "RTE" => array(
                    "notNewRecords" => 1,
                    "RTEonly" => 1,
                    "type" => "script",
                    "title" => "Full screen Rich Text Editing",
                    "icon" => "wizard_rte2.gif",
                    "script" => "wizard_rte.php",
                )
            )
        );
    }

    /**
     * Checks if $articleTypeUid can be found in $articleTypeUids
     * @param int $articleTypeUid Article type to checked
     * @param string $articleTypeUids  Comma separated list of article types to search $articleTypeUid in
     * @return bool true if TSConfig setting was found, else false
     */
    private static function checkTSConfigArticleTypesSetting($articleTypeUid, $articleTypeUids) {
        return in_array($articleTypeUid, t3lib_div::trimExplode(',', $articleTypeUids));
    }


    /// set publish_date when article changed from hidden=1 to hidden=0 and publish_date isn't set
    /**
     *  (checks starttime too); data is added to $fieldArray (so for typo3 save hook usage only)
     */
    private static function addPublishDateIfNotSet($id, &$fieldArray) {
        if (!self::articleWasUnhidden($fieldArray)) return;

        if (self::getAttributeFromFieldArrayOrArticle($fieldArray, $id, 'publish_date') > 0) {
            return; // publish date has been set already
        }

        $starttime = self::getAttributeFromFieldArrayOrArticle($fieldArray, $id, 'starttime');

        $fieldArray['publish_date'] = max(time(), $starttime); // change publish_date
    }

    private static function articleWasUnhidden(array $fieldArray) {
        return
            (isset($_REQUEST['hidden_status']) && $_REQUEST['hidden_status'] == 0) || // workflow button was used to publish the article
            (isset($fieldArray['hidden']) && $fieldArray['hidden'] == 0);
    }

    private static function getAttributeFromFieldArrayOrArticle(array $fieldArray, $article_uid, $attribute) {
        if (isset($fieldArray[$attribute])) {
            return $fieldArray[$attribute];
        }

        if (intval($article_uid) == 0) {
            return 0;   // new article
        }

        $article = new tx_newspaper_Article(intval($article_uid)); // get article
        return $article->getAttribute($attribute);
    }


    /**
	 * Calls TYPO3 savehooks for the articles in order to reflect changes
	 * processed in savehooks (dependency tree etc.)
	 */
	public function callTypo3Savehooks() {
		// prepare datamap
		// \todo: is using tstamp ok? the tstamp gets actually stored ...
		$datamap['tx_newspaper_article'][$this->getUid()] = array('tstamp' => time());

		// use datamap, so all save hooks get called
        /** @var $tce t3lib_TCEmain */
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->start($datamap, array());
		$tce->process_datamap();
		if (count($tce->errorLog)){
			throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
		}
	}


    ////////////////////////////////////////////////////////////////////////////
    //
    //	private data members
    //
    ////////////////////////////////////////////////////////////////////////////
    /// tx_newspaper_Source the tx_newspaper_Article is read from
    private $source = null;
    /// Object to delegate operations to
    private $articleBehavior = null;

    /// list of fields of the tx_newspaper_article table
    private static $article_fields = array();

    ///	List of attributes that together constitute an Article
    /** \todo update */
    private static $attribute_list = array(
        'title', 'teaser', 'bodytext', 'author'
    );

    /// Mapping of the attributes to the names they have in the tx_newspaper_Source for each supported tx_newspaper_Source type
    /** Form of the array:
     *  \code
     *  source_class_name => array (
     * 		attribute_name => name_of_that_attribute_in_source
     * 		...
     *  )
     * \endcode
     * the attributes from \p $attribute_list must be the same as here.
     *
     * \todo update
     */
    private static $mapFieldsToSourceFields = array(
        'tx_newspaper_taz_RedsysSource' => array(
            'title' => 'Titel',
            'teaser' => 'Titel2',
            'bodytext' => 'Text',
            'ressort' => 'OnRes',
//            'author' => 'Autor',
        ),
        'tx_newspaper_DBSource' => array(
            'title' => 'article_manualtitle',
            'teaser' => 'article_title2',
            'bodytext' => 'article_manualtext',
            'ressort' => 'ressort',
            'author' => 'author'
        )
    );
    ///	Additional info needed to instantiate an article for each supported Source type
    private static $table = array(
        'tx_newspaper_taz_RedsysSource' => '',
        'tx_newspaper_DBSource' => 'tx_hptazarticle_list'
    );

    private static $render_hooks = array();

    private static $additionalAttributeConfig = array(); // Additional attribute can be registered with tx_newspaper_article::register::registerAdditionalAttribute()

}

tx_newspaper::registerSaveHook(new tx_newspaper_Article());
?>