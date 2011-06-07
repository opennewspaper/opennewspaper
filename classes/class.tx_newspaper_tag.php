<?php
/**
 *  \file class.tx_newspaper_tag.php
 *
 *  This file is part of the TYPO3 extension "newspaper".
 *
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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
 *  \author Oliver Schroeder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */

/// Tag
/** \todo document me!
 */
class tx_newspaper_Tag implements tx_newspaper_StoredObject {

	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}

    /// Creates a new content tag
    /** \return tx_newspaper_tag object
     */
    public static function createContentTag($value = null) {
        $tag = new tx_newspaper_Tag();
        $tag->setAttribute('tag_type', self::getContentTagType());
        $tag->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        if ($value) {
            $tag->setAttribute('tag', $value);
        }
        return $tag;
    }


    /// Creates a new control tag
    /**
     * \param $controlTagCat uid of control tag category record
     * \param $tag name of tag
     * \param $title name of dossier etc. associated with this control tag
     * \param $section uid of section associated with this control tag
     * \return tx_newspaper_tag object
     */
    public static function createControlTag($controlTagCat, $tag, $title='', $section=0) {
        $newTag = new tx_newspaper_tag();
        $newTag->setAttribute('tag_type', self::getControlTagType());
        $newTag->setAttribute('ctrltag_cat', intval($controlTagCat));
        $newTag->setAttribute('tag', $tag);
        $newTag->setAttribute('title', $title);
        $newTag->setAttribute('section', intval($section));
        $newTag->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
        return $newTag;
    }

    /// \return Array with all control tag categories
    public static function getAllControltagCategories() {
    	return tx_newspaper::selectRows(
    		'uid,title',
    		self::ctrltag_cat_table,
    		'1' . tx_newspaper::enableFields(self::ctrltag_cat_table),
    		'',
    		'sorting'
    	);
    }

    /// \return Array with all control tags for given $category
    public static function getAllControlTags($category) {
    	$category = intval($category);
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'tag_type=' . self::getControltagType() . ' AND ctrltag_cat=' . $category  . tx_newspaper::enableFields(self::tag_table),
    		'',
    		'tag'
    	);
    	$tags = array();
    	foreach($rows as $row) {
    		$tags[] = new tx_newspaper_tag($row['uid']);
    	}
    	return $tags;
    }


	/// \return Array with all content tags
    public static function getAllContentTags() {
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'tag_type=' . self::getContentTagType() . tx_newspaper::enableFields(self::tag_table),
    		'',
    		'tag'
    	);
    	$tags = array();
    	foreach($rows as $row) {
    		$tags[] = new tx_newspaper_tag($row['uid']);
    	}
    	return $tags;
    }

	/**
	 * The \p $tagToBeMerged is merged with the current tag, i.e. Articles which
     * were tagged with \p $tagToBeMerged are tagged with \c $this.
	 * The \p $tagToBeMerged is deleted and set to \c null.
	 * \param tx_newspaper_tag $tagToBeMerged
	 * \return true, if merge was successful, else false
	 * \todo: tx_newspaper_extra_image_tags_mm if tag are to be used in images
	 */
    public function merge(tx_newspaper_tag &$tagToBeMerged) {

    	if ($this->getUid() == $tagToBeMerged->getUid()) {
    		return false; // no need to merge into itself ...
    	}

    	// remove tag relations where this tag AND target tag are attached ()would create a duplicate entry otherwise)
        $articleUids = $this->getArticlesForTag();
//t3lib_div::devlog('tag::merge() get articles', 'newspaper', 0, array('articleUids' => $articleUids, 'q' => tx_newspaper::$query));

        $mergedArticleUids = $tagToBeMerged->getArticlesForTag();

        $tagToBeMerged->removeTagFromArticles($articleUids);
//t3lib_div::devlog('tag::merge() delete duplicates', 'newspaper', 0, array('q' => tx_newspaper::$query));

        $this->updateRelationsForMergedTag($tagToBeMerged);
        //t3lib_div::devlog('tag::merge() merge tag', 'newspaper', 0, array('q' => tx_newspaper::$query));

        $tagToBeMerged->updateDependencyTree($mergedArticleUids);

		// delete tag to be merged
		if (!$tagToBeMerged->delete()) {
			return false;
		}
//t3lib_div::devlog('tag::merge() delete tag to be merged', 'newspaper', 0, array('q' => tx_newspaper::$query));
		$tagToBeMerged = null; // set tag to null, this tag does not exist anymore

        return true;
    }

    /// \return Array with all tag zones (key = tag zone uid)
    public static function getAllTagZones() {
    	$row = tx_newspaper::selectRows(
    		'uid,name',
    		self::tagzone_table,
    		'1' . tx_newspaper::enableFields(self::tagzone_table),
    		'',
    		'name'
    	);
    	$tagzones = array();
    	for ($i = 0; $i < sizeof($row); $i++) {
			$tagzones[$row[$i]['uid']] = $row[$i];
    	}
    	return $tagzones;
    }

    /// \return Name of tag zone for given $uid
    public static function getTagZoneName($uid) {
    	$uid = intval($uid);
    	$row = tx_newspaper::selectRows(
    		'name',
    		self::tagzone_table,
    		'uid=' . $uid . tx_newspaper::enableFields(self::tagzone_table)
    	);
		if ($row) {
    		return $row[0]['name'];
		} else {
			return '';
		}
    }

    /// \return Name of tag zone for given $uid
    public function getCategoryName() {
    	$row = tx_newspaper::selectRows(
    		'title',
    		'tx_newspaper_ctrltag_category',
    		'uid=' . $this->getAttribute('ctrltag_cat') . tx_newspaper::enableFields('tx_newspaper_ctrltag_category')
    	);
    	if ($row) {
    		return $row[0]['title'];
		} else {
			return '';
		}
	}

    /// \param $tz_uid uid of taz zone
    /// \return Array with Extras assigned to tag zone identified by $tz_uid
    public function getTagzoneExtras($tz_uid) {
    	$rows = tx_newspaper::selectRows(
    		'extra',
    		self::ctrltag_to_extra,
    		'tag=' . $this->getUid() . ' AND tag_zone=' . intval($tz_uid) . tx_newspaper::enableFields(self::ctrltag_to_extra),
    		'',
    		'sorting'
    	);
    	$extras = array();
    	foreach($rows as $row) {
    		$extras[] = tx_newspaper_Extra_Factory::getInstance()->create($row['extra']);
    	}
    	return $extras;
    }

    /**
     *  \param $extra Extra object
     *  \param $tz_uid uid of tag zone
     *  \return true id record was written else false
     */
    public function addExtraToTagzone(tx_newspaper_extra $extra, $tz_uid) {
    	$tz_uid = intval($tz_uid);
    	if (!$tz_uid) {
            return false;
    	}

        $uid = tx_newspaper::insertRows(
            self::ctrltag_to_extra,
            array_merge(
                array(
                    'tag' => $this->getUid(),
                    'extra' => $extra->getExtraUid(),
                    'tag_zone' => $tz_uid,
                    'pid' => tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_tag()),
                ),
                tx_newspaper::getDefaultFieldValues(array('crdate', 'tstamp', 'cruser_id'))
            )
        );
        return true;
    }

	/**
	 * Fetches all articles assigned to this tag
	 * \param $limit Limits the number of articles (default is 1000000)
	 * \return Array with articles
	 */
    public function getArticles($limit = 1000000, $start = 0) {
        $article_list = self::getDossierArticleList();
        if (!$article_list) {
            return $this->getArticlesDirect($limit, $start);
        }

        return $this->getArticlesForDossierTag($article_list, $limit, $start);
    }

    private function getArticlesForDossierTag(tx_newspaper_ArticleList $article_list, $limit, $start) {
        $this->setDossierGETParameter();
        $articles = $article_list->getArticles($limit, $start);
        self::resetDossierGETParameter();

        return $articles;
    }

    private static function getDossierArticleList() {

        $section = self::getDossierSection();

        if ($section instanceof tx_newspaper_Section) {
            return $section->getArticleList();
        }

        return null;
    }

    private static function getDossierSection() {
        try {
            return tx_newspaper_Section::getSectionForTypo3Page(tx_newspaper::getDossierPageID());
        } catch (tx_newspaper_IllegalUsageException $e) {
            return null;
        }
    }

    private static $saved_get = array();
    private function setDossierGETParameter() {
        self::$saved_get = $_GET;
        $_GET[tx_newspaper::getDossierGETParameter()] = $this->getUid();
    }

    private static function resetDossierGETParameter() {
        $_GET = self::$saved_get;
        self::$saved_get = array();
    }

    /**
	 * Fetches all articles assigned to this tag, direct datanase access
	 * \param $limit Limits the number of articles (default is 1000000)
	 * \return Array with articles
	 */
    private function getArticlesDirect($limit = 1000000, $start = 0) {
        $select_method_strategy = SelectMethodStrategy::create(true);
        $results = tx_newspaper::selectRows(
            $select_method_strategy->fieldsToSelect(),
            'tx_newspaper_article, tx_newspaper_article_tags_mm',
            'tx_newspaper_article_tags_mm.uid_foreign=' . $this->getUid() . ' AND tx_newspaper_article.uid=tx_newspaper_article_tags_mm.uid_local',
            '',
            'publish_date DESC',
            ($limit > 0)? "$start, $limit" : ''
        );

        $articles = array();
        foreach ($results as $row) {
            $articles[] = $select_method_strategy->createArticle($row);
        }

        return $articles;
    }



    /**
     * Gets the section associated to this control tag
     * \return Section object for control tag, false, if content tag
     */
    public function getSection() {
		if ($this->getAttribute('tag_type') != self::getControlTagType()) {
			return false;
		}
		return new tx_newspaper_section($this->getAttribute('section'));
    }

    /**
     * Detaches this tag from all records tags can be attached to
     */
    public function detach() {
    	foreach (self::$mmTables as $table) {
			tx_newspaper::deleteRows(
				$table,
				array($this->getUid()),
				'uid_foreign'
			);
    	}
    }

    /**
     * Checks if given title is unique for control tags (always true for content tags)
     * @param $title Title to check for uniqueness
     * @return true if title is unique, else false
     */
    public function isTitleUnique($title) {
    	if (!$title) {
    		return false; // empty title not allowed
    	}
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'title LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($title, self::tag_table) . ' AND ' . '
    			uid<>' . $this->getUid() . ' AND ' .
    			'tag_type=' . self::getControlTagType() .
    			tx_newspaper::enableFields(self::tag_table)
    	);
		return (sizeof($rows) == 0);
    }

    /**
     * Checks if given tag name is unique (category aren't checked for control tags, must be unique for all categories)
     * @param $tagName Tag name to check for uniqueness
     * @return true if tag name is unique, else false
     */
    public function isTagUnique($tagName) {
    	if (!$tagName) {
    		return false; // empty tag name not allowed
    	}
    	$rows = tx_newspaper::selectRows(
    		'uid',
    		self::tag_table,
    		'tag LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($tagName, self::tag_table) . ' AND ' . '
    			uid<>' . $this->getUid() . ' AND ' .
    			'tag_type=' . $this->getTagType() .
    			tx_newspaper::enableFields(self::tag_table)
    	);
		return (sizeof($rows) == 0);
    }

    /// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
			return $this->getAttribute('tag');
		} catch (tx_newspaper_Exception $e) {
			return 'Unstored tx_newspaper_Tag object';
		}
	}

	public function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*',
				tx_newspaper::getTable($this),
				'uid=' . $this->getUid() . tx_newspaper::enableFields(tx_newspaper::getTable($this))
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
		if (!$this->attributes && $this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}

		$this->attributes[$attribute] = $value;
	}

	/**
	 * Renames the tag (can only be called on tag already stored)
	 * @param $newName New tag name
	 */
	public function storeRenamedTag($newName) {
		if (!$this->getUid()) {
			throw new tx_newspaper_IllegalUsageException('storeRenamedTag() can only be called for tags that has been stored already');
		}
		// update record
		$data['tstamp'] = time();
		$data['tag'] = $newName;
		tx_newspaper::updateRows($this->getTable(), 'uid=' . $this->getUid(), $data); // store data
		$this->setAttribute('tag', $newName);
	}

	/**
     * Stores a tag and prevents duplicate tags by checking content and type.
     * @return uid
     * @throws tx_newspaper_IllegalUsageException
     */
	public function store() {
        $this->checkTagValid();

		if (!$this->attributes) $this->readAttributesFromDB();

        $result = $this->readThisFromDB();

        if(count($result) > 0) {
			// process existing tag
            $this->setUid($result[0]['uid']); // record is already stored in db, so get uid of that record

            // check if data was updated
			$data = array_diff($this->attributes, $result[0]);
			if ($data) {
				// record needs to be updated ...
				$data['tstamp'] = time(); // ... so update tstamp
				tx_newspaper::updateRows($this->getTable(), 'uid=' . $this->getUid(), $data); // and store data
			}
        } else {
            tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));
        	$this->setUid(tx_newspaper::insertRows($this->getTable(), $this->attributes));
        }
        return $this->getUid();
	}

    /// check if tag has been stored in the database already
    private function readThisFromDB() {
        $where = 'tag = \'' . $this->getAttribute('tag') . '\' AND tag_type = ' . $this->getAttribute('tag_type');
        if ($this->getAttribute('tag_type') === self::getControlTagType()) {
            $where .= ' AND ctrltag_cat = ' . intval($this->getAttribute('ctrltag_cat'));
        }

        return tx_newspaper::selectRows('*', $this->getTable(), $where);
    }

    private function checkTagValid() {
        if (!$this->getAttribute('tag') || trim($this->getAttribute('tag')) === '' || !$this->getAttribute('tag_type')) {
            $message = '[tag: \'' . $this->getAttribute('tag') . '\', type: \'' . !$this->getAttribute('tag_type') . '\]';
            $message = 'Can not store tag, it has no content or type. ' . $message;
            throw new tx_newspaper_IllegalUsageException($message);
        }

        if (self::getControlTagType() === intval($this->getAttribute('tag_type'))) {
            if (!$this->getAttribute('ctrltag_cat')) {
                throw new tx_newspaper_IllegalUsageException('[tag: \'' . $this->getAttribute('tag') . '\' has no category');
            }
        }
    }

    public function getTagType() {
		return $this->getAttribute('tag_type');
	}

	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	}

	public function getUid() {
		return intval($this->uid);
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * Deletes the tag (tag is detached from other records first)
	 */
	public function delete() {
		$this->detach();
		$success = tx_newspaper::updateRows(
			$this->getTable(),
			'uid=' . $this->getUid(),
			array('deleted' => 1)
		);
		if ($success) $this->setUid(0);
        return $success;
	}


	/**
	 * Calls TYPO3 savehooks for all articles this tag is attached to
	 */
	public function callTypo3SavehooksForArticles() {
		$articles = $this->getArticlesDirect();
		foreach($articles as $article) {
			$article->callTypo3Savehooks();
		}
	}

	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }

	static public function getModuleName() { return 'np_tag'; }


	/// Get all tag zones (array with uids) this tag is assigned to
	public function getTagZones() {
    	return tx_newspaper::selectRows(
    		'DISTINCT tag_zone',
    		self::ctrltag_to_extra,
    		'tag=' . $this->getUid() . tx_newspaper::enableFields(self::ctrltag_to_extra)
    	);
	}

	/// Given a partial tag, return all possible completions for that tag
	/** \param $fragment A string to interpret as a part of a tag
	 *  \param $max Maximum number of hints returned
	 *  \param $start_only If \c true, returns only tags beginning with
	 *  	\p $fragment
	 *  \param $strong If \c true, fatten the requested portion of the tag that
	 * 		has been searched for.
	 *  \return Array of tags (UIDs as key and tagname as value) that match \p $fragment
	 */
	static public function getCompletions($fragment,
										  $max = 0,
										  $start_only = false,
										  $strong = false) {
		$results = tx_newspaper::selectRows(
			'uid, tag', 'tx_newspaper_tag',
			'tag LIKE \'' . ($start_only? '': '%') . $fragment . '%\'',
			'', '', ($max? ('0, ' . $max): '')
		);

		$return = array();

		if ($results) {
			foreach ($results as $row) {
				$return[$row['uid']] = str_replace($fragment,
										($strong? '<strong>': '') . $fragment . ($strong? '</strong>': ''),
										$row['tag']);
			}
		}

		return $return;
	}


	////////////////////////////////////////////////////////////////////////////

	// Tag type handling

	/// SQL table storing tags
	const tag_table = 'tx_newspaper_tag';

	/// SQL table storing control tag categories
	const ctrltag_cat_table = 'tx_newspaper_ctrltag_category';

	/// SQL table storing tag zones
	const tagzone_table = 'tx_newspaper_tag_zone';

	/// SQL table assigning control tags and extras to tag zones
	const ctrltag_to_extra = 'tx_newspaper_controltag_to_extra';

    const article2tag_table = 'tx_newspaper_article_tags_mm';

	/// Get control tag type
	/** \return 2 hard coded
     */
	public static function getControlTagType() {
		return 2; // hard coded
	}

	/// Checks if a content tag is already in use
	/** \param $tag Name of tag
	 *  \return true if content tag $tag is already stored in the database
	 */
	public static function doesContentTagAlreadyExist($tag) {
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid',
			'tx_newspaper_tag',
			'tag LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($tag, 'tx_newspaper_tag') .
				' AND tag_type=' . self::getContentTagType()
		);
		return (isset($row['uid']) && $row['uid'] > 0);
	}

	/// Checks if a control tag and control tag category combination is already in use
	/** \param $tag Name of tag
	 *  \param $ctrltagtype uid of control tag type
	 *  \return true if control tag $tag is already stored in the database for given $ctrltagtype
	 */
	public static function doesControlTagAlreadyExist($tag, $ctrltagtype) {
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid',
			'tx_newspaper_tag',
			'tag LIKE ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($tag, 'tx_newspaper_tag') .
				' AND tag_type=' . self::getControlTagType() .
				' AND ctrltag_cat = '.intval($ctrltagtype)
		);
		return (isset($row['uid']) && $row['uid'] > 0);
	}


	/// Get the content tag type
	/// \return 1 (hard coded)
    public static function getContentTagType() {
		return 1; // hard coded
    }

	////////////////////////////////////////////////////////////////////////////

    /// get article uids with this tag attached
    private function getArticlesForTag() {
        $articleUids = tx_newspaper::selectRows(
            'uid_local',
            self::article2tag_table,
            'uid_foreign=' . $this->getUid()
        );

        for ($i = 0; $i < sizeof($articleUids); $i++) {
            $articleUids[$i] = $articleUids[$i]['uid_local'];
        }

        return $articleUids;
    }

    /// remove tag to be merged from articles where this (=target) tag has already been attached
    private function removeTagFromArticles(array $articleUids) {
        if ($articleUids) {
            tx_newspaper::deleteRows(
                self::article2tag_table,
                'uid_foreign=' . $this->getUid() . ' AND uid_local IN (' . implode(',', $articleUids) . ')'
            );
        }
    }

   	/// move article tag relation to current tag (from tag to be merged)
    private function updateRelationsForMergedTag(tx_newspaper_Tag $tagToBeMerged) {
        tx_newspaper::updateRows(
            self::article2tag_table,
            'uid_foreign=' . $tagToBeMerged->getUid(),
            array('uid_foreign' => $this->getUid())
        );
    }

    private function updateDependencyTree(array $mergedArticleUids)  {
        if (tx_newspaper_DependencyTree::useDependencyTree()) {
            foreach ($mergedArticleUids as $uid) {
                $article = new tx_newspaper_Article($uid);
                $tree = tx_newspaper_DependencyTree::generateFromArticle($article, array($this));
                $tree->executeActionsOnPages('tx_newspaper_Article');
            }
        }
    }

	private $uid = ''; ///< UID that identifies the tag in the DB

	// tables where tag attchment is stored
	private static $mmTables = array(
		'tx_newspaper_article_tags_mm',
		'tx_newspaper_extra_image_tags_mm',
	);
}
