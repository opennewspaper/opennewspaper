<?php
/**
 *  \file class.tx_newspaper_article.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlebehavior.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_tag.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_smarty.php');

/// An article for the online newspaper
/** The article is the central entity in a newspaper. All other functionalities
 *  deal with displaying articles, lists of articles or additional information
 *  linked to articles.
 * 
 *  Data-wise, an article consists of the minimum set of fields that every
 *  article must have. All additional data connected to an article (e.g. images,
 *  links, tags, media, ...) are called "Extra" and linked to an article. The
 *  class representing Extras is tx_newspaper_Extra and its descendants.
 * 
 *  The Extras must be placed in an Article or in a PageZone. 
 */
class tx_newspaper_Article extends tx_newspaper_PageZone 
	implements tx_newspaper_ArticleIface, tx_newspaper_WritesLog {

	const article_related_table = 'tx_newspaper_article_related_mm';
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	magic methods ( http://php.net/manual/language.oop5.magic.php )
	//
	////////////////////////////////////////////////////////////////////////////

	/// Create a tx_newspaper_Article
	/** Initializes the tx_newspaper_ArticleBehavior and tx_newspaper_Smarty
	 *  used as auxiliaries.
	 * 
	 *  Ensures that the current object has a record identifying it in the 
	 *  persistent storage as tx_newspaper_Extra and tx_newspaper_PageZone.
	 */
	public function __construct($uid = 0) {
		$this->articleBehavior = new tx_newspaper_ArticleBehavior($this);
		$this->smarty = new tx_newspaper_Smarty();
		
		if ($uid) {
			$this->setUid($uid);
			
			$this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());	
			$this->pagezone_uid = $this->createPageZoneRecord();
		}
		
	}

	///	Things to do after an article is cloned
	/** This magic function is called after all attributes of a
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
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n" .
			   'extras: ' . print_r($this->extras, 1) . "\n";
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
		
		/// insert article data (if uid == 0) or update if uid > 0
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			if (!$this->attributes) { 
				$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
			}
			
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			$this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}

		/// Ensure the page zone has an entry in the abstract supertable...
		$pagezone_uid = $this->createPageZoneRecord($this->getUid(), $this->getTable());
		/// ... and is attached to the correct page
		if ($this->getParentPage() && $this->getParentPage()->getUid()) {
			tx_newspaper::updateRows(
				'tx_newspaper_pagezone', 
				'uid = ' . $pagezone_uid, 
				array('page_id' => $this->getParentPage()->getUid())
			);
		}
		
		/// store all extras and make sure they are in the MM relation table
		if ($this->extras) foreach ($this->extras as $extra) {
			$extra_uid = $extra->store();
			$extra_table = $extra->getTable();
			$this->relateExtra2Article($extra);
		}
		
		return $this->getUid();		
	}
	

	public function getUid() { 
		if (!intval($this->uid)) $this->uid = $this->attributes['uid'];
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
	/** \param $template_set Template set to use
	 */
	public function render($template_set = '') {

		/// Default articles should never contain text that is displayed.
		if ($this->getAttribute('is_template')) return;
		
		/** Check whether to use a specific template set.
		 *	This must be done regardless if this is a template used to define
		 *	default placements for articles, or an actual article.
		 */
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}
		
		/// Configure Smarty rendering engine.
		if ($template_set) {
			$this->smarty->setTemplateSet($template_set);
		}
		if ($this->getCurrentPage()->getUID() && $this->getCurrentPage()->getPageType()) {
			$this->smarty->setPageType($this->getCurrentPage());
		}
		if ($this->getPageZoneType()) {
			$this->smarty->setPageZoneType($this);
		}

		/** Assemble the text paragraphs and extras in an array of the form:
		 *  \code
		 *  array(
		 *  	$paragraph_number => array(
		 * 			"text" => $text_of_paragraph,
		 *    		"spacing" => {0, 1, 2, ...},             // for empty paragraphs after text
		 *          "extras" => array(
		 *				$position => array(
         *					"extra_name" => get_class(),
         * 					"content" => $rendered_extra
       	 *				),
		 * 		 		...
		 * 			)
		 * 		),
		 * 		...
		 *  )
		 *  \endcode
		 */
		$text_paragraphs = $this->splitIntoParagraphs();
		$paragraphs = array();
		$spacing = 0;
		foreach ($text_paragraphs as $index => $text_paragraph) {

			$paragraph = array();
			if (trim($text_paragraph)) {
				$paragraph['text'] = $text_paragraph;
				$paragraph['spacing'] = intval($spacing);
				$spacing = 0;
				foreach ($this->getExtras() as $extra) {
					if ($extra->getAttribute('paragraph') == $index ||
						sizeof($text_paragraphs)+$extra->getAttribute('paragraph') == $index) {
						$paragraph['extras'][$extra->getAttribute('position')] = array();
						$paragraph['extras'][$extra->getAttribute('position')]['extra_name'] = $extra->getTable();
						$paragraph['extras'][$extra->getAttribute('position')]['content'] .= $extra->render($template_set);
					}
				}
				/*  Braindead PHP does not sort arrays automatically, even if
				 *  the keys are integers. So if you, e.g., insert first $a[4]
				 *  and then $a[2], $a == array ( 4 => ..., 2 => ...).
				 *  Thus, you must call ksort.
				 */
				if ($paragraph['extras']) ksort($paragraph['extras']);
				$paragraphs[] = $paragraph;
			} else {
				//	empty paragraph, increase spacing value to next paragraph
				$spacing++;
			}
		}
			
		/** Make sure all extras are rendered, even those whose \c paragraph
		 *  attribute is greater than the number of text paragraphs or less
		 *  than its negative.
		 */ 		
		foreach ($this->getExtras() as $extra) {
			if ($extra->getAttribute('paragraph')+sizeof($text_paragraphs) < 0) {
				$paragraphs[0]['extras'][] = $extra->render($template_set);
			} else if ($extra->getAttribute('paragraph') > sizeof($text_paragraphs)) {
				$paragraphs[sizeof($paragraphs)-1]['extras'][] = $extra->render($template_set);
			}
		}

#t3lib_div::devlog('$paragraphs', 'newspaper', 0, $paragraphs);
		$this->smarty->assign('paragraphs', $paragraphs);
		$this->smarty->assign('attributes', $this->attributes);
		$ret = $this->smarty->fetch($this);

		return $ret;
	}

	/// Read data from table \p $table with UID \p $uid
	/** \param $uid UID of the record to read
	 *  \param $table SQL table to read record from
	 *  \return The data contained in the requested record
	 *  \todo remove.
	 */ 
	static public function readExtraItem($uid, $table) {
		if (!$uid) return array();
		return tx_newspaper::selectOneRow('*', $table, 'uid=' . $uid);
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_WithSource
	//
	////////////////////////////////////////////////////////////////////////////

	public function getSource() { return $this->source; }

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

	static public function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::mapFieldToSourceField($fieldname, $source,
																   self::$mapFieldsToSourceFields);
	}
	
	static public function sourceTable(tx_newspaper_Source $source) {
		return tx_newspaper_ArticleBehavior::sourceTable($source, self::$table);
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ArticleIface
	//
	////////////////////////////////////////////////////////////////////////////
	
	public function importieren(tx_newspaper_Source $quelle) {
		$this->articleBehavior->importieren($quelle);
	}

	public function exportieren(tx_newspaper_Source $quelle) {
		$this->articleBehavior->exportieren($quelle);
	}

	public function laden() {
		$this->articleBehavior->laden();
	}

	public function vergleichen() {
		$this->articleBehavior->vergleichen();
	}

	public function extraAnlegen() {
		$this->articleBehavior->extraAnlegen();
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_PageZone
	//
	////////////////////////////////////////////////////////////////////////////
	
	/// Get the list of tx_newspaper_Extra associated with this Article in sorted order
	/** The Extras are sorted by attribute \c paragraph first and
	 *  \c position second.
	 * 
	 * \param $extra_class The desired type of tx_newspaper_Extra, either as
	 *  	object or as class name
	 */
	public function getExtras() { 
		if (!$this->extras) {
			$extras = tx_newspaper::selectRows(
				'uid_foreign', 'tx_newspaper_article_extras_mm', 
				'uid_local = ' . $this->getUid());
			if ($extras) foreach ($extras as $extra) {
				try {
					$show = tx_newspaper::selectOneRow('show_extra', 
												   'tx_newspaper_extra', 
												   'uid = ' . $extra['uid_foreign']);
					if (TYPO3_MODE != 'BE' && !$show['show_extra']) continue; 

					$new_extra = tx_newspaper_Extra_Factory::getInstance()->create($extra['uid_foreign']);
					$this->extras[] = $new_extra;
				} catch(tx_newspaper_EmptyResultException $e) {
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

	/// Add an extra after the Extra which is on the original page zone as $origin_uid
	/** Reimplemented from tx_newspaper_PageZone because concrete Articles don't 
	 *  have PageZones which inherit from them; default article are to be 
	 *  treated like PageZones. Setting \p $recursive on an concrete Article 
	 *  would result in an error.
	 */
	public function insertExtraAfter(tx_newspaper_Extra $insert_extra,
									 $origin_uid = 0, $recursive = true) {
		tx_newspaper_PageZone::insertExtraAfter($insert_extra, $origin_uid, $this->isDefaultArticle());
	}

	/// Get the tx_newspaper_PageZoneType associated with this Article
	/** \return The tx_newspaper_PageZoneType associated with this Article. If
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
	/** \param $extra_class The desired type of tx_newspaper_Extra, either as
	 *  	object or as class name
	 *  \return The first tx_newspaper_Extra of the given class (by appearance
	 * 		in article), or \c null.
	 */
	public function getFirstExtraOf($extra_class) {
		$extras = $this->getExtrasOf($extra_class);
		if (sizeof($extras) > 0) return $extras[0];		
		return null;
	}

	/// Get article type of article
	/** \return tx_newspaper_ArticleType assigned to this Article
	 */
	public function getArticleType() {
		return new tx_newspaper_ArticleType($this->getAttribute('articletype_id'));
	}

	/// checks if article is a default article or a concrete article
	/** \return \c true if article is a default article (else \c false).
	 */
	public function isDefaultArticle() {
		return $this->getAttribute('is_template');
	}
	
	/// Delete all Extras
	public function clearExtras() {
		$this->extras = array();	
	}

	/// Write record in MM table relating an Extra to this article
	/** The MM table record is only written if it did not exist beforehand.
	 * 
	 *  If \p $extra did not have a record in the abstract Extra table
	 *  (\c tx_newspaper_extra ), the record is created.
	 * 
	 *  The MM-table \c tx_newspaper_article_extra_mm will contain an
	 *  association between the Article's UID and the UID in the abstract Extra
	 *  table.
	 * 
	 *  \param $extra The tx_newspaper_Extra to add to \c $this.
	 * 
	 *  \return The UID of \p $extra in the abstract Extra table.
	 */	
	public function relateExtra2Article(tx_newspaper_ExtraIface $extra) {
		
		$extra_table = tx_newspaper::getTable($extra);
		$extra_uid = $extra->getUid();
		$article_uid = $this->getUid();

		$abstract_uid = $extra->getExtraUid();
		if (!$abstract_uid)	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $extra_table); 
		
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
	
	/// Change the paragraph of an Extra recursively on inheriting Articles
	/** This function is used to change the paragraph of an Extra on Articles
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
	 *  \param $extra tx_newspaper_Extra which should be moved to another
	 * 		paragraph.
	 *  \param $new_paragraph The paragraph to which \p $extra is moved.
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
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
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
				if ($copied_extra) $inheriting_pagezone->changeExtraParagraph($copied_extra, $new_paragraph);
			}
		}
	}
	
	/// Generates a URL which links to the tx_newspaper_Article on the correct tx_newspaper_Page.
	/** \param $section Section from which the link is generated. Defaults to
	 * 		using the article's primary section.
	 *  \param $pagetype tx_newspaper_PageType of the wanted tx_newspaper_Page.
	 *  \todo Handle links to articles in sections other than their primary section.
	 *  \todo Handle PageType other than article page.
	 *  \todo Check if target page has an Article display Extra,
	 * 		tx_newspaper_Extra_DisplayArticles. 
	 */
	public function getLink(tx_newspaper_Section $section = null, 
							tx_newspaper_PageType $pagetype = null) {
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
		else {
			$typo3page = $section->getTypo3PageID();	
		}
		
		return tx_newspaper::typolink_url(
			array(
				'id' => $typo3page,
				tx_newspaper::article_get_parameter => $this->getUid()
		));
	}
	
	/// Get a list of all attributes in the tx_newspaper_Article table.
	/** \return All attributes in the tx_newspaper_Article table.
	 *  \todo A static attribute list sucks. Determine it dynamically.
	 */
	static public function getAttributeList() { return self::$attribute_list; }
	
	
	/// Gets a list of tx_newspaper_Article objects assigned to given article type
	/** \param $at tx_newspaper_ArticleType object
	 *  \param $limit max number of records to read (default: 10), if negative no limit is used
	 *  \return array with tx_newspaper_Article objects 
	 */
	static public function listArticlesWithArticletype(
		tx_newspaper_ArticleType $at, $limit = 10
	) {

		$limit = intval($limit);
		$limit_part = ($limit > 0)? '0,' . $limit : ''; 
		
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
	/** The new tx_newspaper_Section will be inserted after existing sections.
	 *  The Article is listed in tx_newspaper_Section \p $s afterwards.
	 * 
	 *  \param $s New Section
	 */
	public function addSection(tx_newspaper_Section $s) {
/// \todo: if ($this->getuid() == 0) throw e OR 		
/// \todo: just collect here and store sections later in article::store()
		
		// get pos of next element 
		$p = tx_newspaper::getLastPosInMmTable('tx_newspaper_article_sections_mm', $this->getUid()) + 1;
		
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
	/** \param $uids UIDs of the tx_newspaper_Section s which \c $this will
	 *  	belong to.
	 */
	public function setSections(array $uids) {
t3lib_div::devlog('setSections()', 'newspaper', 0, array($uids));
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

	/// Get the list of tx_newspaper_Section s to which the current article belongs
	/** \param $limit Maximum number of tx_newspaper_Section s to find
	 *  \param $sorted If set, the sections gets sorted level-wise
	 *  \return List of tx_newspaper_Section s to which the current article belongs
	 */
	public function getSections($limit = 0, $sorted=false) {
		$section_ids = tx_newspaper::selectRows(
			'uid_foreign',
			'tx_newspaper_article_sections_mm',
			'uid_local = '.$this->getUid(),
			'',
			'',
			$limit? "0, $limit": ''
		);
		
		$sections = array();
		foreach ($section_ids as $id) {
			$sections[] = new tx_newspaper_Section($id['uid_foreign']);
		}
		return $sections;
	}

	/// Get the primary tx_newspaper_Section of a tx_newspaper_Article.
	/** \return The tx_newspaper_Section in which \p $this is displayed by
	 *  	default, if no Section context is given, or \c null.
	 */		
	public function getPrimarySection() {
		$sections = $this->getSections(1);
		if (sizeof($sections) == 0) {
			return null; // no section found
		}
		return $sections[0];
	}


	/// Gets a list of (configured but) missing extras in the article
	/** It is checked if extras placed on the default article are missing in
	 *  the concrete article and if extras configured as must-have or should-
	 *  have are missing in the article.
	 *  \return array of Extra objects (either existing extras or newly created empty extras)
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
		foreach($tsc_extras as $tsc_extra) {
//t3lib_div::devlog('getMissingDefaultExtras()', 'newspaper', 0, array('e' => $tsc_extra));
			list($extra_class, $paragraph) = explode(':', $tsc_extra);
			$paragraph = intval($paragraph);

			if (tx_newspaper::classImplementsInterface($extra_class, 'tx_newspaper_ExtraIface')) {
				if (!$this->checkExtra($extra_class)) {
					$e = new $extra_class();
					$shortcuts[] = array(
						'extra_class' => $extra_class,
						'paragraph' => $paragraph,
						'title' => $e->getTitle(),
					);
				}
			} // \todo: log errors?
		}	

/*
		$must_should_have_extras = array_unique(array_merge($at->getTSConfigSettings('musthave'), $at->getTSConfigSettings('shouldhave')));
		$must_should_have_extras_simple = array();
		foreach($must_should_have_extras as $extra) {
			$tsc = explode(':', $extra);
			$must_should_have_extras_simple[] = $tsc[0]; // cut off value for position, if set 
		}
//t3lib_div::devlog('getMissingDefaultExtras() mustshouldhave 1', 'newspaper', 0, array('mse' => $must_should_have_extras, 'mse_simple' => $must_should_have_extras_simple));

		// get extras on default article for the primary section of this article
		if (!$primarySection = $this->getPrimarySection()) {
			// no primary section found, so no section assigned, so no extra missing ...
			return array();
		}
		if (!$defaultArticle = $primarySection->getDefaultArticle()) {
			// no default article found, so no extras missing ...
			return array();	
		}
		$defaultExtras = $defaultArticle->getExtras();
		
		
//t3lib_div::devlog('getMissingDefaultExtras() default extras', 'newspaper', 0, array('de' => $defaultExtras));
		if (is_array($defaultExtras)) {
			// get extras assigned to this article
			$concreteExtras = $this->getExtras();
			/// check which default extras are already assigned to this article
			foreach($defaultExtras as $keyDefault => $defaultExtra) {
				foreach($concreteExtras as $keyConcrete => $concreteExtra) {
					if ($defaultExtra->getOriginUid() == $concreteExtra->getOriginUid()) {
						/// default extra found in concrete article, so no shortcut needed
						unset($defaultExtras[$keyDefault]);

						// check if this extra type is a must or should have extra 
						$key = array_search($defaultExtra->getTable(), $must_should_have_extras_simple);
						if ($key !== false) {
							// an extra of this type was found, so no shortcut needed for this extra type
							unset($must_should_have_extras[$key]);
							unset($must_should_have_extras_simple[$key]);
						}
					}
				}
			}
		}
//t3lib_div::devlog('getMissingDefaultExtras() mustshouldhave 2', 'newspaper', 0, array('mse' => $must_should_have_extras));
		
		/// search for extras that are already assigned to the article (and are configured as must-have or should-have extra but are NOT placed in the default article)
		foreach($concreteExtras as $keyConcrete => $concreteExtra) {
			$key = array_search($concreteExtra->getTable(), $must_should_have_extras_simple);
			if ($key !== false) {
				// an extra of this type was found, so no shortcut needed for this extra type
				unset($must_should_have_extras[$key]);
				unset($must_should_have_extras_simple[$key]);
			}
		}
		

		/// create shortcuts for missing "default" extras in the concrete article
		foreach($defaultExtras as $defaultExtra) {
			$shortcuts[] = $defaultExtra;
			// check if this extra type is a must or should have extra 
			$key = array_search($defaultExtra->getTable(), $must_should_have_extras_simple);
			if ($key !== false) {
				// an extra of this type was found, so no shortcut needed
				unset($must_should_have_extras[$key]);
				unset($must_should_have_extras_simple[$key]);
			}
		}
//t3lib_div::devlog('getMissingDefaultExtras() mustshouldhave 3', 'newspaper', 0, array('mse' => $must_should_have_extras, 's' => $shortcuts));

		/// create shortcuts for remaining must-have / should-have extras
		// the array holds extra class names, not objects!
		foreach($must_should_have_extras_simple as $key => $extra_class) {
			if (class_exists($extra_class)) {
				$tsc = explode(':', $extra_class);
				$e = new $extra_class($tsc[0]);
				if (sizeof($tsc) > 1) {
					$e->setAttribute('paragraph', intval($tsc[1])); // add paragraph for extra
				}
				$shortcuts[] = $e;
			} else {
				t3lib_div::devlog('tx_newspaper_Article::getMissingDefaultExtras()', 'newspaper', 2, array('Class ' . $extra_class . ' unknown. Was set as must-have or should-have extra for article type ' . $at->getAttribute('title') . ' in section ' . $this->getPrimarySection()->getAttribute('section_name')));
			}
		}
*/
//t3lib_div::devlog('getMissingDefaultExtras() shortcuts', 'newspaper', 0, array('s' => $shortcuts));		
		return $shortcuts;
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
		foreach($this->getExtras() as $extra) {
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

	
	/// Get the SQL table which associates tx_newspaper_Extra with tx_newspaper_PageZone.
	public function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

    /**
     * @param  $tagtype int defaults to contentTagType
     * @return array
     */
    public function getTags($tagtype = null) {
        if(!$tagtype) {
            $tagtype = tx_newspaper::getContentTagType();
        }
        $where .= " AND tag_type = ".$tagtype;
        $where .= " AND uid_local = ".$this->getUid();
        $tag_ids = tx_newspaper::selectMMQuery('uid_foreign', $this->getTable(),
            'tx_newspaper_article_tags_mm', 'tx_newspaper_tag', $where);

		$tags = array();
		foreach ($tag_ids as $id) {
			$tags[] = new tx_newspaper_Tag($id['uid_foreign']);
		}
		return $tags;
    }

    public function getRelatedArticles() {
        t3lib_div::devlog('RelatedArticles()', 'newspaer', 0);
    	
        $rows = tx_newspaper::selectRows(
            self::article_related_table . '.uid_local, ' . self::article_related_table .'.uid_foreign',
            self::article_related_table .
                ' JOIN ' . $this->getTable() . ' AS a_local' .
                ' ON ' . self::article_related_table . '.uid_local = a_local.uid' .
                ' JOIN ' . $this->getTable() . ' AS a_foreign' .
                ' ON ' . self::article_related_table . '.uid_foreign= a_foreign.uid',
            '(uid_local = ' . $this->getUid() .
                ' OR uid_foreign = ' . $this->getUid() . ')' .
                ' AND (a_foreign.hidden = 0 AND a_local.hidden = 0)'
        );
        t3lib_div::devlog('RelatedArticles', 'newspaer', 0, array(tx_newspaper::$query, $rows));
        
        $related_articles = array();
            
        foreach ($rows as $row) {
            if (intval($row['uid_local']) == $this->getUid()) {
                if (intval($row['uid_foreign']) != $this->getUid()) {
                    $related_articles[] = new tx_newspaper_Article(intval($row['uid_foreign']));
                }
            } else if ($row['uid_foreign'] == $this->getUid()) {
                if (intval($row['uid_local']) != $this->getUid()) {
                    $related_articles[] = new tx_newspaper_Article(intval($row['uid_local']));
                }
            }
        }
        
        return array_unique($related_articles);
    }

    /// Make sure that an article related to \c $this has also \c $this as relation. 
    private function ensureRelatedArticlesAreBidirectional() {
    	        
    	foreach ($this->getRelatedArticles() as $related_article) {
	    	$row = tx_newspaper::selectZeroOrOneRows(
	    	    'uid_local', self::article_related_table, 
	    	    'uid_foreign = ' . $this->getUid() . ' AND uid_local = ' . $related_article->getUid());
            t3lib_div::devlog('ensureRelatedArticlesAreBidirectional', 'newspaer', 0, tx_newspaper::$query);
	    	if ($row) continue;
	    	
	    	$relation_to_write = array(
	    	    'uid_local' => $related_article->getUid(),
	    	    'uid_foreign' => $this->getUid()
	    	);
	    	tx_newspaper::insertRows(self::article_related_table, $relation_to_write);
    	}   	    
    }
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	Typo3 hooks
	//
	////////////////////////////////////////////////////////////////////////////
	
	/** \todo some documentation would be nice ;-) */
    public static function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
        self::joinTags($incomingFieldArray, $table, $id, $that);
    }

	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		self::addPublishDateIfNotSet($status, $table, $id, $fieldArray); // check if publish_date is to be added
		self::makeRelatedArticlesBidirectional($id);
	}

    public static function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
        self::modifyTagSelection($table, $field);
    }

	public static function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
		if (strtolower($table) == 'tx_newspaper_article') {
			// hide default articles in list module, only concrete article are visible in list module
			$additionalWhereClause .= ' AND is_template=0';
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
        if($table == 'tx_newspaper_article' && isset($incomingFieldArray['tags']) && isset($incomingFieldArray['tags_ctrl'])) {
          $tags = $incomingFieldArray['tags'];
          $ctrlTags = $incomingFieldArray['tags_ctrl'];
          if($ctrlTags) {
              $tags = explode(",",$tags);
              $ctrlTags = explode(",",$ctrlTags);
              $allTags = implode(",", array_merge($tags, $ctrlTags));
              $incomingFieldArray['tags'] = $allTags;
              $_REQUEST['data'][$table][$id]['tags'] = $allTags;
          }
        }
    }

    /// Make sure that an article related to \c $article_uid has also \c $article_uid as relation. 
    private static function makeRelatedArticlesBidirectional($article_uid) {
    	if (!intval($article_uid)) return;
        $article = new tx_newspaper_Article(intval($article_uid));

        try {
        	$article->getAttribute('uid');
        } catch (tx_newspaper_Exception $e) {
        	return;
        }
        
        $article->ensureRelatedArticlesAreBidirectional();
        
    }
    
    private static function modifyTagSelection($table, $field) {
        if('tx_newspaper_article' === $table && 'tags' === $field) {
//t3lib_div::devLog('modifyTagSelection()', 'newspaper', 0, array('table' => $table, 'field' => $field));            
            global $TCA;
            $TCA['tx_newspaper_article']['columns']['tags']['config']['type'] = 'user';
            $TCA['tx_newspaper_article']['columns']['tags']['config']['userFunc'] = 'tx_newspaper_be->renderTagControlsInArticle';
        }

    }
	
	/// set publish_date when article changed from hidden=1 to hidden=0 and publish_date isn't set (checks starttime too); data is added to $fieldArray
	private static function addPublishDateIfNotSet($status, $table, $id, &$fieldArray) {
//t3lib_div::devlog('addPublishDateIfNotSet()', 'newspaper', 0, array('time()' => time(), 'status' => $status, 'table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, '_request' => $_REQUEST));
		if (strtolower($table) == 'tx_newspaper_article' && 
			(
				(isset($_REQUEST['hidden_status']) && $_REQUEST['hidden_status'] == 0) || // workflow button was used to publish the article
				(isset($fieldArray['hidden']) && $fieldArray['hidden'] == 0) // called by self::setPublishDateIfNeeded() or hidden field is configured to be visible in backend
			)
		) {
			
			// hidden is 0, so article was just made visible
			
			$article = null; // might be needed later

			// if the values for starttime or publish_date are set in $fieldArray, these values MUST be used 
			// (because these new values aren't stored in the database)

			if (isset($fieldArray['publish_date'])) {
				// publish_date is available in $fieldArray, no need to read from database
				$publish_date = $fieldArray['publish_date'];
			} else {
				// publish_date has to be retrieved
				if (intval($id)) {
					// article was stored before ...
					$article = new tx_newspaper_article(intval($id)); // get article
					$publish_date = $article->getAttribute('publish_date');
				} else {
					// new article
					$publish_date = 0; 
				}
			}
			
			if ($publish_date > 0) {
				return; // publish date has been set already 
			}

			if (isset($fieldArray['starttime'])) {
				// starttime is available in $fieldArray
				$starttime = $fieldArray['starttime'];
			} else {
				// starttime has to be retrieved
				if (intval($id)) {
					// article was stored before ...
					if (!($article instanceof tx_newspaper_article)) {
						$article = new tx_newspaper_article(intval($id)); // get article
					}
					$starttime = $article->getAttribute('starttime');
				} else {
					// new article, $id equal NEW_something
					$starttime = 0; // if timestart would have been set, it would be part of $fieldArray
				}
			}

			$fieldArray['publish_date'] = max(time(), $starttime); // change publish_date
		}
			
	}
	
	/// sets the publish date if needed
	/** The publish date needs to be set if the attribute hidden changes to 0 and
	 *  no publish_date is stored so far.
	 *  This method just SETS the publish_date attribute, but DOES NOT store the article
	 */
	public function setPublishDateIfNeeded() {
		$fakeFieldArray = array(
			'hidden' => $this->getAttribute('hidden')
		);
		self::addPublishDateIfNotSet('', $this->getTable(), $this->getUid(), $fakeFieldArray);
		if (isset($fakeFieldArray['publish_date'])) { 
			// addPublishDateIfNotSet() added a new publish_date in $fakeFieldArray, so set that date as publish_date
			$this->setAttribute('publish_date', $fakeFieldArray['publish_date']);
			$this->setAttribute('tstamp', time());
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
		return new tx_newspaper_Page($section, new tx_newspaper_PageType($_GET));
	}
	
	/// Split the article's text into an array, one entry for each paragraph
	/** tx_newspaper_Extra are inserted before or after paragraphs. This
	 *  function splits the article text so the position of a tx_newspaper_Extra
	 *  can be found.
	 *   
	 *  The functionality of this function depends on the way the RTE stores
	 *  line breaks. Currently it breaks the text at \c "<p>/</p>" -pairs and 
	 *  also at line breaks \c ("\n").
	 * 
	 *  \attention If the format of line breaks changes, this function must be
	 * 	altered.
	 */
	protected function splitIntoParagraphs() {
		/** A text usually starts with a \c "<p>", in that case the first paragraph
		 *  must be removed. It may not be the case though, if so, the first
		 *  paragraph is meaningful and must be kept.
		 */
		$temp_paragraphs = explode('<p', $this->getAttribute('text'));
		$paragraphs = array();		
		
		foreach ($temp_paragraphs as $paragraph) {
			/// Remove the test of the \c "<p>" - tag from every line.
			$paragraph = trim(substr($paragraph, strpos($paragraph, '>')+1));
			/** Each paragraph now should end with a \c "</p>". If it doesn't, the
			 *  text is not well-formed. In any case, we must remove the \c "</p>".
			 */
			$paragraph = str_replace('</p>', '', $paragraph);
			
			/// Now we split the paragraph at line breaks.
			$sub_paragraphs = explode("\n", $paragraph);
			
			/// Store the pieces in one flat array.
			foreach($sub_paragraphs as $sub_paragraph) $paragraphs[] = $sub_paragraph;
		}

		return $paragraphs;	
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
        $high = sizeof($this->getExtras())-1;
        $low = 0;
       
        while ($high >= $low) {
            $index_to_check = floor(($high+$low)/2);
            $comparison = $this->getExtra($index_to_check)->getAttribute('paragraph') -
            			  $extra->getAttribute('paragraph');
            if ($comparison < 0) $low = $index_to_check+1;
            elseif ($comparison > 0) $high = $index_to_check-1;
            else {
	            $comparison = $this->getExtra($index_to_check)->getAttribute('position') -
	            			  $extra->getAttribute('position');
	            if ($comparison < 0) $low = $index_to_check+1;
	            elseif ($comparison > 0) $high = $index_to_check-1;
	            else return $index_to_check;
            }
        }
		
		// Loop ended without a match
		throw new tx_newspaper_InconsistencyException('Extra ' . $extra->getUid() .
													  ' not found in array of Extras!');		
	}
	

 	/// Ordering function to keep Extras in the same order as they appear on the PageZone
 	/** Supplied as parameter to \c usort() in getExtras().
 	 *  \param $extra1 first tx_newspaper_Extra to compare
 	 *  \param $extra2 second tx_newspaper_Extra to compare
 	 *  \return < 0 if \p $extra1 comes before \p $extra2, > 0 if it comes after, 
 	 * 			== 0 if their position is the same 
 	 */
 	static protected function compareExtras(tx_newspaper_ExtraIface $extra1, 
 									 		tx_newspaper_ExtraIface $extra2) {
 		$paragraph1 = $extra1->getAttribute('paragraph');
 		$paragraph2 = $extra2->getAttribute('paragraph');
 		
 		if ($paragraph1 == $paragraph2) {
 			return $extra1->getAttribute('position')-$extra2->getAttribute('position');
 		}
 		
 		/**	Negative paragraphs are sorted at the end, in reverse order.
 		 *  So, a negative paragraph comes AFTER a positive paragraph. If both
 		 *  are negative though, it's the usual order: The smaller value (higher
 		 *  negative value) comes first.
 		 */ 
 		if ($paragraph1*$paragraph2 < 0) return $paragraph2-$paragraph1;
 		else return $paragraph1-$paragraph2;
 			
	}

	/// SQL table which associates tx_newspaper_Extra s with tx_newspaper_PageZone s
	static protected $extra_2_pagezone_table = 'tx_newspaper_article_extras_mm';
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	private data members
	//
	////////////////////////////////////////////////////////////////////////////
	
	///< tx_newspaper_Source the tx_newspaper_Article is read from
	private $source = null;
	/// Object to delegate operations to
	private $articleBehavior = null;	
	
	///	List of attributes that together constitute an Article
	/** \todo update */
	private static $attribute_list = array(
		'title', 'teaser', 'text', 'author'
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
	    	'text' => 'Text',
	    	'ressort' => 'OnRes',
	    	'author' => 'Autor'
		),
		'tx_newspaper_DBSource' => array(
	    	'title' => 'article_manualtitle',
	    	'teaser' => 'article_title2',
	    	'text' => 'article_manualtext',
	    	'ressort' => 'ressort', 
	    	'author' => 'author'
		)
	);
	
	///	Additional info needed to instantiate an article for each supported Source type
	private static $table = array(
		'tx_newspaper_taz_RedsysSource' => '',
		'tx_newspaper_DBSource' => 'tx_hptazarticle_list'
	);
	
}

//tx_newspaper::registerSaveHook(new tx_newspaper_Article());

?>