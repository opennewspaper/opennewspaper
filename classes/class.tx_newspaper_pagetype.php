<?php
/**
 *  \file class.tx_newspaper_pagetype.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Feb 6, 2009
 */
 
/// Type of the page that is displayed.
/** Newspaper allows for (more or less) freely configurable types of pages for
 *  each tx_newspaper_Section. As always, the concept might best be explained by
 *  examples.
 * 
 *  - The List of articles belonging to a Section is shown on the Section
 *    Overview Page.
 *  - Articles belonging to a Section are displayed on the Article Page.
 *  - An Article may be displayed differently for printing on a Print Page.
 *  - There may be a RSS Page for RSS feeds.
 *  - etc. pp.
 * 
 *  Each of the Pages can be fully configured with Page Zones and Extras,
 *  independently from each other.
 * 
 *  Which Page Type is currently shown in the Frontend, is decided by the GET
 *  parameters the current (Typo3) page is called with. 
 * 
 *  \see tx_newspaper_Page.
 */
class tx_newspaper_PageType implements tx_newspaper_StoredObject {
 	
 	/// Construct a tx_newspaper_PageType given the $_GET array
	/**
	 *	Find out which page type we're on (Section, Article, RSS, Comments, whatever)
	 *  \note If we need to change the mapping of GET-parameters to page types, 
	 *  do it here!
	 *
	 *  - If $_GET['pagetype'] is set, it is the page corresponding to that type
	 *    - The parameter 'pagetype' is in fact configurable via 
	 *      tx_newspaper::GET_pagetype().
	 *  - If any other GET variable configured in the table tx_newspaper_pagetype
	 *    is set, the PageType corresponding to that is instantiated.
	 *  - If $_GET['art'] is set, it is the article page. $_GET['art'] is 
	 *    checked last, so it does not override any other page type which might
	 *    display a specific article.
	 *  - If no GET variable configured to define a page type is set, it is the
	 *    section overview page.
	 */
 	function __construct($get = array()) {
		$this->setSQLCondition($get);
  	}
 	 	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
	}
 	
 	/// Get the SQL \c WHERE -condition used to find this Page Type.
 	/** Page Types have an SQL condition which uniquely identifies them in the
 	 *  DB.
 	 *  \return SQL condition which uniquely identifies the Page Type in the DB.
 	 */
 	public function getCondition() { 
 		return $this->condition . tx_newspaper::enableFields(tx_newspaper::getTable($this)); 
 	}
    public function getGETParameters() {
        return $this->get_parameters;
    }

 	public function getID() { return $this->getAttribute('uid'); }
 	
 	public function getAttribute($attribute) {
 		
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', tx_newspaper::getTable($this), $this->getCondition()
			);
			$this->setUid($this->attributes['uid']);
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
					'*', tx_newspaper::getTable($this), $this->condition
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	/// \return true if page type can be accessed (FE/BE use enableFields)
	public function isValid() {
		// check if page type is valid
		try {
			$this->getAttribute('uid'); // getAttribute forces the object to be read from database
			return true;
		} catch (tx_newspaper_EmptyResultException $e) {
			return false;
		}
	}
	
	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	}

 	public function getTable() { return tx_newspaper::getTable($this); }
	public function getUid() { return intval($this->uid); }
	public function setUid($uid) { $this->uid = $uid; }


	static public function getModuleName() { return 'np_pagetype'; }
 	
	/// get all available page types
	/** \return array of all available page types objects
	 */
	public static function getAvailablePageTypes($condition = '') {
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pt = new tx_newspaper_PageType();
		$row = tx_newspaper::selectRows(
			'*', 
			$pt->getTable(),
			'NOT deleted' // . ' AND pid = ' . $sf->getPid($pt)
			. ($condition? ' AND ' . $condition: '')
		);

		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_PageType(intval($row[$i]['uid']));
		}
		return $list;
	}

    static public function getArticlePageType() {
        $pagetypes = self::getAvailablePageTypes('is_article_page');
        if (!empty($pagetypes)) {
            return array_pop($pagetypes);
        }

        throw new tx_newspaper_InconsistencyException(
            'Tried to get the article pagetype, but none is configured.
            Please ensure that one (and just one) of your pagetype records has the property \'is_article_page\' set.
            Obviously, this pagetype should be associated with pages that display articles.'
        );
    }

    static public function getSectionPageType() {
        if (!is_null(self::getConfiguredSectionPageType())) {
            return self::getConfiguredSectionPageType();
        }

        $pagetypes = self::getAvailablePageTypes("get_var = '' AND get_value = ''");
        if (!empty($pagetypes)) {
            return array_pop($pagetypes);
        }

        throw new tx_newspaper_InconsistencyException(
            'Tried to get the section pagetype, but none is configured.
            You need to either have a page type that has both its \'get_var\' and its \'get_value\' attributes set to empty,
            or configure the section page type via the TSConfig parameter \'newspaper.section_page_type = <uid of pagetype record>\'.
            Obviously, this pagetype should be associated with pages that display section overviews.'
        );
    }
    
	////////////////////////////////////////////////////////////////////////////

    private static function getConfiguredSectionPageType() {
        $ts_config = tx_newspaper::getTSConfig();
        if (intval($ts_config['newspaper.']['section_page_type'])) {
            return new tx_newspaper_PageType(intval($ts_config['newspaper.']['section_page_type']));
        }
        return null;

    }

	/// Derive the SQL condition used to instantiate the type from the parameters the constructor was called with.
	/** \param $input Either a UID for a concrete PageType record or the array
	 *     of $_GET parameters.
	 */ 
	private function setSQLcondition($input) {
		if (is_int($input)) {
			$this->setIntCondition($input);
			return;
 		} else if (!is_array($input)) {
 			throw new tx_newspaper_IllegalUsageException(
				'Argument to generate a page type must be either an integer UID or the _GET array.
				 In fact it is:
				' . print_r($input, 1)
			);
 		}
 		
		$this->setConditionFromGET($input);
	}
	
	/// Set SQL condition for reading a page type with a specified UID.
	private function setIntCondition($uid) {
		$this->setUid($uid); // just read the record (probably for backend)
		$this->condition = 'uid = ' . $this->getUid();
	}
	
	/// Determine the SQL condition from the GET parameters the FE page was called with.
	private function setConditionFromGET(array $input) {
		
		// First check if a page type was explicitly requested.
 		if ($input[tx_newspaper::GET_pagetype()]) { 
			$this->condition = 'get_var = \'' . tx_newspaper::GET_pagetype() .
				'\' AND get_value = \'' . $input[tx_newspaper::GET_pagetype()] . '\'';
            $this->get_parameters[tx_newspaper::GET_pagetype()] = $input[tx_newspaper::GET_pagetype()];
 		} else {
 			
 			// Try to deduce the page type from other GET parameters.
			if ($this->find_in_possible_types($input)) {
                throw new tx_newspaper_IllegalUsageException('Could not determine page type from GET: ' . print_r($input, 1));
            }
			
			// If none is set, check if an article is requested.
			if ($input[tx_newspaper::GET_article()]) {
 				$this->condition = 'get_var = \'' . tx_newspaper::GET_article() .'\'';
			} else {
				
				// Only of no other page type could be determined, show the section page.
				$this->condition = 'NOT get_var';
 			}
 		}
		
	}
	
	/// Create the Page Type from all possible, non-special registered types
	/** The \em special Types are: article page, section overview page, and all
	 *  page types created with the "pagetype" GET-parameter (which is defined
	 *  in tx_newspaper). 
	 * 
	 *  Called from the constructor, sets \c $this->condition if a non-special
	 *  registered type is found.
	 * 
	 *  \param $get The array used to initialize the page type (\c $_GET).
	 *  \return \c true if a page type could be initialized, \c false otherwise.
	 */
 	private function find_in_possible_types(array $get) {
 		
 		//  try all page types other than the article page first 
 		foreach (self::getNonSpecialPageTypes() as $type) {
			$get_var = $type['get_var'];

 			// transform $get[skpc[sc]] to $get[skpc][sC]
			if (strpos($get_var, ']') !== false) { 				
				$parts = explode('[', $get_var);
				foreach($parts as $key => $part) $parts[$key] = rtrim($part, ']');
					
				$get_var = array($parts[0], $parts[1]);
			}
				
			$temp_get = $get;
			while (is_array($get_var) and sizeof($get_var) > 1) {
				$temp_get = $temp_get[array_shift($get_var)];
			} 
		 	if (is_array($get_var)) $get_var = $get_var[0];
			if ($temp_get[$get_var]) {
 				$this->condition = 'get_var = \'' . $type['get_var'] .'\'';
 				return true;
 			}
 		}
 		
 		return false;
 	}

	/// Non-special meaning, types that don't have a special meaning in the system
	private static function getNonSpecialPageTypes() {
 		return tx_newspaper::selectRows(
 			'DISTINCT get_var', tx_newspaper::getTable('tx_newspaper_PageType'),
 			'get_var != \'' . tx_newspaper::GET_pagetype() .'\' AND ' .
 			'get_var != \'' . tx_newspaper::GET_article() .'\' AND ' .
 			'get_var != \'\''
 		);
		
	} 
	
 	private $uid = 0;
 	private $condition = '1';
 	private $attributes = array();
    private $get_parameters = array();
}
?>