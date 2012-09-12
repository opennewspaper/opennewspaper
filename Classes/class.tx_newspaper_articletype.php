<?php
/**
 *  \file class.tx_newspaper_articletype.php
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
 *  \author Oliver Sch�rder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */
 
/// Type of tx_newspaper_Article
/** Articles can have several types, which determine the way the Articles are
 *  displayed and also which tx_newspaper_Extras should be present in an Article
 *  by default. 
 * 
 *  This is best explained by an Example.
 *  - Standard Articles should always have an Image with them.
 *  - Columns don't necessarily have an Image (but they may), but they need a
 * 	  list of links.
 *  - Editorials should be displayed in a different design.
 * 
 *  Article Types must be defined previous to using them. This is done via the 
 *  Typo3 list module. Once an Article Type is defined, its properties can be
 *  set in TSConfig.
 */
class tx_newspaper_ArticleType implements tx_newspaper_StoredObject {
	
	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
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
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
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

    public function getTypeName() { return $this->getAttribute('title'); }

	/// Get TSConfig setting for this article type
	/** \param $type article type configured in TSConfig, currently
	 *      available: musthave, shouldhave
	 *  \return array containing configuration: [class name][:paragraph][,[classname][:paragraph]][...,[classname][:paragraph]]
	 */
	public function getTSConfigSettings($type) {
		/// check tsconfig in article sysfolder
		$sysfolder = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_article()); 
		$tsc = t3lib_BEfunc::getPagesTSconfig($sysfolder);

		if (!isset($tsc['newspaper.']['articletype.'][$this->getAttribute('normalized_name') . '.'][$type])) {
			return array(); // no settings found
		}

		$setting = t3lib_div::trimExplode(',', $tsc['newspaper.']['articletype.'][$this->getAttribute('normalized_name') . '.'][$type]);

		return $setting;
	}
	
	
	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }
	
	static public function getModuleName() { return 'np_articletype'; }

    /**
     * Get ALL article types
     * @static
     * @return array Sorted list of ALL available article types
     */
    static public function getArticleTypes() {
		$pid = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_ArticleType());
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_articletype',
			'pid=' . $pid,
			'',
			'sorting'
		);
		$articleTypes = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$articleTypes[] = new tx_newspaper_ArticleType($row[$i]['uid']);
		}
		return $articleTypes;
	}

    /**
     * Get article types allowed for the current backend user.
     * $articleTypesToBeMerged will be added if the user doesn't have permission to access these article types
     * User TSConfig:
     * newspaper.accessArticleTypes = [comma separated list of article typ uids]
     * @static
     * @param Array $articleTypesToBeMerged Article types that should be merged in the the set of allowed article types
     * @return Array Article types accessible for current BE user
     */
    public static function getArticleTypesRestricted(array $articleTypesToBeMerged=array()) {
        $allowedArticleTypeUids = self::getAllowedArticleTypeUids();
        if (!is_array($allowedArticleTypeUids)) {
            return self::getArticleTypes(); // No User TSConfig set, so ALL article types can be accessed
        }

        $accessibleArticleTypes = array();
        foreach(self::getArticleTypes() as $at) {
            if (in_array($at->getUid(), $allowedArticleTypeUids)) {
                $accessibleArticleTypes[] = $at; // Access to this article type is granted
            } else {
                foreach($articleTypesToBeMerged as $mergedAt) {
                    if ($at->getUid() == $mergedAt->getUid()) {
                        // This feature is needed the prevent problems for be users while working with an article with
                        // an article types assigned that IS NOT permitted for that be user.
                        $accessibleArticleTypes[] = $at; // User can access this article type even though it's not TSConfigured
                        break;
                    }
                }
            }
        }
        return $accessibleArticleTypes;
    }

    /**
     * Called by TCEForms as itemsProcFunc to fill the article type dropdown according to User TSConfig restrictions
     * A sorted list of article types that can be accessed by the current BE user is copied into
     * $params['items'] (formatted according to TCEForms requirements)
     * @param $params Array passed by TCEForms, see http://typo3.org/documentation/document-library/core-documentation/doc_core_tca/current/
     * @return void
     */
    public function processArticleTypesForArticleBackend(&$params) {
//t3lib_div::debug($params, 'params');
        $currentArticleType = intval($params['row']['articletype_id'])?
            array(new tx_newspaper_ArticleType(intval($params['row']['articletype_id']))) : array();
        $allowedArticleTypes = array();
        foreach(self::getArticleTypesRestricted($currentArticleType) as $at) {
            // 0=>title, 1=>uid, 2=>icon file (nit used here, so empty)
            $allowedArticleTypes[] = array('0' => $at->getAttribute('title'), '1' => $at->getUid(), '2' => '');
        }
        $params['items'] = $allowedArticleTypes;
    }

    /**
     * Return User TSConfig setting (or false if no setting can be found)
     * newspaper.accessArticleTypes = [comma separated list of article typ uids]
     * @static
     * @return Array with article type uids OR false, if no User TSConfig could be found
     */
    public static function getAllowedArticleTypeUids() {
        if (!isset($GLOBALS['BE_USER'])) {
            return false; // Doesn't make sense without a backend user ...
        }
        if (!$tsc = $GLOBALS['BE_USER']->getTSConfigVal('newspaper.accessArticleTypes')) {
            return false; // No User TSConfig found
        }
        return t3lib_div::trimExplode(',', $tsc);
    }


	////////////////////////////////////////////////////////////////////////////
	
	private $uid = ''; ///< UID that identifies the article type
}
